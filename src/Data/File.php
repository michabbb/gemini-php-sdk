<?php

declare(strict_types=1);

namespace Gemini\Data;

use Gemini\Contracts\Arrayable;
use Gemini\Enums\FileState;

final class File implements Arrayable
{
    public function __construct(
        public readonly string    $name,
        public readonly ?string   $displayName,
        public readonly string    $mimeType,
        public readonly string    $sizeBytes,
        public readonly string    $createTime,
        public readonly string    $updateTime,
        public readonly ?string   $expirationTime,
        public readonly ?string   $sha256Hash,
        public readonly ?string   $uri,
        public readonly FileState $state,
        public readonly ?array    $error = null,
        public readonly ?array    $videoMetadata = null,
    )
    {
    }

    public static function from(array $attributes): self
    {
        return new self(
            name          : $attributes['name'],
            displayName   : $attributes['displayName'] ?? null,
            mimeType      : $attributes['mimeType'],
            sizeBytes     : $attributes['sizeBytes'],
            createTime    : $attributes['createTime'],
            updateTime    : $attributes['updateTime'],
            expirationTime: $attributes['expirationTime'] ?? null,
            sha256Hash    : $attributes['sha256Hash'] ?? null,
            uri           : $attributes['uri'] ?? null,
            state         : FileState::from($attributes['state']),
            error         : $attributes['error'] ?? null,
            videoMetadata : $attributes['videoMetadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
                                'name'           => $this->name,
                                'displayName'    => $this->displayName,
                                'mimeType'       => $this->mimeType,
                                'sizeBytes'      => $this->sizeBytes,
                                'createTime'     => $this->createTime,
                                'updateTime'     => $this->updateTime,
                                'expirationTime' => $this->expirationTime,
                                'sha256Hash'     => $this->sha256Hash,
                                'uri'            => $this->uri,
                                'state'          => $this->state->value,
                                'error'          => $this->error,
                                'videoMetadata'  => $this->videoMetadata,
                            ], fn($value) => $value !== null);
    }
}