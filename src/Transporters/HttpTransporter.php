<?php /** @noinspection UnknownInspectionInspection */

declare(strict_types=1);

namespace Gemini\Transporters;

use Closure;
use Gemini\Contracts\TransporterContract;
use Gemini\Exceptions\ErrorException;
use Gemini\Exceptions\TransporterException;
use Gemini\Exceptions\UnserializableResponse;
use Gemini\Foundation\Request;
use Gemini\Requests\FileManager\UploadFileRequest;
use Gemini\Transporters\DTOs\ResponseDTO;
use GuzzleHttp\Exception\ClientException;
use Http\Discovery\Psr17Factory;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpTransporter implements TransporterContract
{
    /**
     * Creates a new Http Transporter instance.
     *
     * @param array<string, string> $headers
     * @param array<string, string|int> $queryParams
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly string          $baseUrl,
        private readonly array           $headers,
        private readonly array           $queryParams,
        private readonly Closure         $streamHandler,
    )
    {
        // ..
    }

    /**
     * {@inheritDoc}
     */
    public function request(Request|UploadFileRequest $request): ResponseDTO
    {
        $queryParams = $this->queryParams;

        if ($this->isFileUploadRequest($request) === 'upload') {
            $queryParams = array_merge($queryParams, $request->getQueryParams());
        }

        $psrRequest = $request->toRequest(
            baseUrl    : $this->baseUrl,
            headers    : $this->headers,
            queryParams: $queryParams
        );

        $uploadCommand = $this->isFileUploadRequest($request);

        if ($uploadCommand === 'start' || $uploadCommand === 'upload') {
            $uploadRequest = $this->createUploadRequest($request, $psrRequest, $uploadCommand);

            $response      = $this->sendRequest(
                fn(): ResponseInterface => $this->client->sendRequest(request: $uploadRequest)
            );

            if ($uploadCommand === 'start') {
                return ResponseDTO::from(data: $response->getHeaders());
            }
        } else {
            $response = $this->sendRequest(
                fn(): ResponseInterface => $this->client->sendRequest(request: $psrRequest)
            );
        }

        $contents = $response->getBody()->getContents();
        $this->throwIfJsonError(response: $response, contents: $contents);

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new UnserializableResponse($jsonException);
        }

        return ResponseDTO::from(data: $data);
    }

    private function createUploadRequest(UploadFileRequest $request, RequestInterface $psrRequest, string $uploadCommand): RequestInterface
    {
        $psr17Factory  = new Psr17Factory();
        $uploadRequest = $psr17Factory->createRequest($request->getMethod()->value, $psrRequest->getUri());
        if ($uploadCommand === 'upload') {
            $uploadRequest = $uploadRequest->withBody($request->getFileStream());
        } elseif ($psrRequest->getBody() !== null) {
            $uploadRequest = $uploadRequest->withBody($psrRequest->getBody());
        }
        foreach ($this->headers as $name => $value) {
            $uploadRequest = $uploadRequest->withHeader($name, $value);
        }
        foreach ($request->getHeaders() as $name => $value) {
            $uploadRequest = $uploadRequest->withHeader($name, $value);
        }
        return $uploadRequest;
    }

    private function isFileUploadRequest(Request $request): ?string
    {
        if (
            $request instanceof UploadFileRequest &&
            array_key_exists('X-Goog-Upload-Command', $request->getHeaders())
        ) {
            return match ($request->getHeaders()['X-Goog-Upload-Command']) {
                'start'            => 'start',
                'upload, finalize' => 'upload',
                default            => throw new \RuntimeException('Invalid X-Goog-Upload-Command: ' . $request->getHeaders()['X-Goog-Upload-Command']),
            };
        }
        return null;
    }

    /**
     * @throws \Exception
     */
    public function requestStream(Request $request): ResponseInterface
    {
        $response = $this->sendRequest(
            fn(): ResponseInterface => ($this->streamHandler)($request->toRequest(baseUrl: $this->baseUrl, headers: $this->headers, queryParams: $this->queryParams))
        );

        $this->throwIfJsonError(response: $response, contents: $response);

        return $response;
    }

    /**
     * @throws ErrorException
     * @throws UnserializableResponse|\Gemini\Exceptions\TransporterException
     */
    private function sendRequest(Closure $callable): ResponseInterface
    {
        try {
            return $callable();
        } catch (ClientExceptionInterface $clientException) {
            if ($clientException instanceof ClientException) {
                $this->throwIfJsonError($clientException->getResponse(), $clientException->getResponse()->getBody()->getContents());
            }

            throw new TransporterException($clientException);
        }
    }

    /**
     * @throws UnserializableResponse
     * @throws ErrorException
     */
    private function throwIfJsonError(ResponseInterface $response, string|ResponseInterface $contents): void
    {
        if ($response->getStatusCode() < 400) {
            return;
        }

        if ($contents instanceof ResponseInterface) {
            $contents = $contents->getBody()->getContents();
        }

        try {
            /** @var array{error?: array{code: int, message: string, status: string } } $response */
            $response = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

            if (isset($response['error'])) {
                throw new ErrorException($response['error']);
            }
        } catch (JsonException $jsonException) {
            throw new UnserializableResponse($jsonException);
        }
    }
}
