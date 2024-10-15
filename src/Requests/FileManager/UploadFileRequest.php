<?php

declare(strict_types=1);

namespace Gemini\Requests\FileManager;

use Gemini\Enums\Method;
use Gemini\Foundation\Request;
use Gemini\Requests\Concerns\HasJsonBody;
use Psr\Http\Message\StreamInterface;

class UploadFileRequest extends Request
{
    use HasJsonBody;

    public Method $method  = Method::POST;
    public array  $headers = [];

    public function __construct(
        private readonly string          $displayName,
        private readonly StreamInterface $fileStream,
        private readonly string          $mimeType,
        private readonly int             $fileSize,
        ?array                           $headers = null,
        private string                   $uri = '',
        private array                    $queryParams = []
    )
    {
        if ($headers !== null) {
            $this->headers = $headers;
        }
    }

    public function resolveEndpoint(): string
    {
        return 'upload/v1beta/files';
    }

    public function body(): array
    {
        return [
            'file' => ['display_name' => $this->displayName]
        ];
    }

    public function getFileStream(): StreamInterface
    {
        return $this->fileStream;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function withHeaders(array $headers): self
    {
        $clone          = clone $this;
        $clone->headers = $headers; // Assuming you have a $headers property

        return $clone;
    }

    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function setQueryParams(array $queryParams): void
    {
        $this->queryParams = $queryParams;
    }

}