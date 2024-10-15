<?php

namespace Gemini\Testing\Resources;


use Gemini\Data\UploadedFile;
use Gemini\Resources\FileManager;
use Gemini\Testing\Resources\Concerns\Testable;

class FileManagerTestResource
{
    use Testable;

    protected function resource(): string
    {
        return FileManager::class;
    }

    /**
     * @throws \Throwable
     */
    public function uploadFile(string $filePath, string $displayName, string $mimeType): UploadedFile
    {
        $record = $this->record(method: __FUNCTION__, args: func_get_args());
        if ($record instanceof UploadedFile) {
            return $record;
        }
        throw new \RuntimeException('Invalid response');
    }
}