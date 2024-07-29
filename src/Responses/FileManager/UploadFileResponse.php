<?php

declare(strict_types=1);

namespace Gemini\Responses\FileManager;

use Gemini\Contracts\Arrayable;
use Gemini\Data\File;

final class UploadFileResponse implements Arrayable
{
    public function __construct(
        public readonly File $file,
    )
    {
    }

    public static function from(array $attributes): self
    {
        return new self(
            file: File::from($attributes['file'])
        );
    }

    public function toArray(): array
    {
        return [
            'file' => $this->file->toArray(),
        ];
    }
}