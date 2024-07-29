<?php

declare(strict_types=1);

namespace Gemini\Requests\FileManager;

use Gemini\Enums\Method;
use Gemini\Foundation\Request;
use Gemini\Requests\Concerns\HasJsonBody;

class UploadFileRequest extends Request
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $filePath,
        protected readonly string $displayName,
        protected readonly ?string $mimeType = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "upload/v1beta/files";
    }

    public function defaultBody(): array
    {
        $fileContent = file_get_contents($this->filePath);
        $mimeType = $this->mimeType ?? mime_content_type($this->filePath);

        return [
            'metadata' => json_encode([
                                          'file' => [
                                              'displayName' => $this->displayName,
                                          ]
                                      ]),
            'file' => [
                'content' => base64_encode($fileContent),
                'mimeType' => $mimeType,
            ],
        ];
    }

    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    public function getQuery(): array
    {
        return [
            'uploadType' => 'multipart',
        ];
    }
}