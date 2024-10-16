<?php /** @noinspection UnknownInspectionInspection */

declare(strict_types=1);

namespace Gemini\Testing;

use Gemini\Contracts\Arrayable;
use Gemini\Contracts\ClientContract;
use Gemini\Contracts\ResponseContract;
use Gemini\Enums\ModelType;
use Gemini\Exceptions\ErrorException;
use Gemini\Responses\StreamResponse;
use Gemini\Testing\Requests\TestRequest;
use Gemini\Testing\Resources\ChatSessionTestResource;
use Gemini\Testing\Resources\EmbeddingModelTestResource;
use Gemini\Testing\Resources\FileManagerTestResource;
use Gemini\Testing\Resources\GenerativeModelTestResource;
use Gemini\Testing\Resources\ModelTestResource;
use PHPUnit\Framework\Assert as PHPUnit;
use Throwable;

class ClientFake implements ClientContract
{
    /**
     * @var array<array-key, TestRequest>
     */
    private array $requests = [];

    /**
     * @param  array<array-key, ResponseContract|ErrorException>  $responses
     */
    public function __construct(protected array $responses = [])
    {
    }

    public function fileManager(): FileManagerTestResource
    {
        return new FileManagerTestResource($this);
    }


    /**
     * @param  array<array-key, ResponseContract>  $responses
     */
    public function addResponses(array $responses): void
    {
        $this->responses = [...$this->responses, ...$responses];
    }

    public function assertSent(string $resource, ModelType|string|null $model = null, callable|int|null $callback = null): void
    {
        if (is_int($callback)) {
            $this->assertSentTimes(resource: $resource, model: $model, times: $callback);

            return;
        }

        PHPUnit::assertTrue(
            $this->sent(resource: $resource, model: $model, callback: $callback) !== [],
            "The expected [{$resource}] request was not sent."
        );
    }

    private function assertSentTimes(string $resource, ModelType|string|null $model = null, int $times = 1): void
    {
        $count = count($this->sent(resource: $resource, model: $model));

        PHPUnit::assertSame(
            $times, $count,
            "The expected [{$resource}] resource was sent {$count} times instead of {$times} times."
        );
    }

    /**
     * @return mixed[]
     */
    private function sent(string $resource, ModelType|string|null $model = null, ?callable $callback = null): array
    {
        if (! $this->hasSent(resource: $resource, model: $model)) {
            return [];
        }

        $callback = $callback ?: fn (): bool => true;

        return array_filter($this->resourcesOf(type: $resource), fn (TestRequest $request) => $callback($request->method(), $request->args()));
    }

    private function hasSent(string $resource, ModelType|string|null $model = null): bool
    {
        return $this->resourcesOf(type: $resource, model: $model) !== [];
    }

    public function assertNotSent(string $resource, ModelType|string|null $model = null, ?callable $callback = null): void
    {
        PHPUnit::assertCount(
            0, $this->sent(resource: $resource, model: $model, callback: $callback),
            "The unexpected [{$resource}] request was sent."
        );
    }

    public function assertNothingSent(): void
    {
        $resourceNames = implode(
            separator: ', ',
            array: array_map(fn (TestRequest $request): string => $request->resource(), $this->requests)
        );

        PHPUnit::assertEmpty($this->requests, 'The following requests were sent unexpectedly: '.$resourceNames);
    }

    /**
     * @return array<array-key, TestRequest>
     */
    private function resourcesOf(string $type, ModelType|string|null $model = null): array
    {
        return array_filter($this->requests, fn (TestRequest $request): bool => $request->resource() === $type && ($model === null || $request->model() === $model));
    }

    /**
     * @throws Throwable
     */
    public function record(TestRequest $request): ResponseContract|StreamResponse|Arrayable
    {
        $this->requests[] = $request;

        $response = array_shift($this->responses);

        if (is_null($response)) {
            /** @noinspection ThrowRawExceptionInspection */
            throw new \Exception('No fake responses left.');
        }

        if ($response instanceof Throwable) {
            throw $response;
        }

        return $response;
    }

    public function models(): ModelTestResource
    {
        return new ModelTestResource(fake: $this);
    }

    public function generativeModel(ModelType|string $model): GenerativeModelTestResource
    {
        return new GenerativeModelTestResource(fake: $this, model: $model);

    }

    public function geminiPro(): GenerativeModelTestResource
    {
        return $this->generativeModel(model: ModelType::GEMINI_PRO);
    }

    public function geminiProVision(): GenerativeModelTestResource
    {
        return $this->generativeModel(model: ModelType::GEMINI_PRO_VISION);
    }

    public function embeddingModel(ModelType|string $model = ModelType::EMBEDDING): EmbeddingModelTestResource
    {
        return new EmbeddingModelTestResource(fake: $this, model: $model);
    }

    public function chat(ModelType|string $model = ModelType::GEMINI_PRO): ChatSessionTestResource
    {
        return new ChatSessionTestResource(fake: $this, model: $model);
    }
}
