<?php

declare(strict_types=1);

namespace Gemini\Contracts\Resources;

use Gemini\Responses\FileManager\UploadFileResponse;

interface FileManagerContract
{
    public function uploadFile(string $filePath, string $displayName, ?string $mimeType = null): UploadFileResponse;
}