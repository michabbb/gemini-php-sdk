<?php

declare(strict_types=1);

namespace Gemini\Data;

use Gemini\Contracts\Arrayable;
use Gemini\Enums\MimeType;

/**
 * URI based data.
 *
 * https://ai.google.dev/api/rest/v1/Content#filedata
 */
final class FileData implements Arrayable
{
    /**
     * @param MimeType|string|null $mimeType Optional. The IANA standard MIME type of the source data.
     * @param string $fileUri Required. URI.
     */
    public function __construct(
        public readonly MimeType|string|null $mimeType,
        public readonly string               $fileUri,
    )
    {
    }

    /**
     * @param array{ mimeType?: string, fileUri: string } $attributes
     */
    public static function from(array $attributes): self
    {
        return new self(
            mimeType: isset($attributes['mimeType']) ? MimeType::from($attributes['mimeType']) : null,
            fileUri : $attributes['fileUri']
        );
    }

    public function toArray(): array
    {
        return array_filter([
                                'mimeType' => $this->mimeType instanceof MimeType ? $this->mimeType->value : $this->mimeType,
                                'fileUri'  => $this->fileUri,
                            ]);
    }
}