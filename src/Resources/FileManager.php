<?php

declare(strict_types=1);

namespace Gemini\Resources;

use Gemini\Contracts\Resources\FileManagerContract;
use Gemini\Contracts\TransporterContract;
use Gemini\Requests\FileManager\UploadFileRequest;
use Gemini\Responses\FileManager\UploadFileResponse;

final class FileManager implements FileManagerContract
{
    public function __construct(
        private readonly TransporterContract $transporter,
    ) {
    }

    public function uploadFile(string $filePath, string $displayName, ?string $mimeType = null): UploadFileResponse
    {
        $request = new UploadFileRequest($filePath, $displayName, $mimeType);
        $response = $this->transporter->request($request);

        return UploadFileResponse::from($response->data());
    }
}