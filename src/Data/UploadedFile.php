<?php

declare(strict_types=1);

namespace Gemini\Data;

use Gemini\Contracts\ResponseContract;
use Gemini\Enums\FileState;

final class UploadedFile implements ResponseContract
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
        public readonly string    $uri,
        public readonly FileState $state,
    ) {
    }

    public static function from(array $attributes): self
    {
        return new self(
            name           : $attributes['file']['name'],
            displayName    : $attributes['file']['displayName'] ?? null,
            mimeType       : $attributes['file']['mimeType'],
            sizeBytes      : $attributes['file']['sizeBytes'],
            createTime     : $attributes['file']['createTime'],
            updateTime     : $attributes['file']['updateTime'],
            expirationTime : $attributes['file']['expirationTime'] ?? null,
            sha256Hash     : $attributes['file']['sha256Hash'] ?? null,
            uri            : $attributes['file']['uri'],
            state          : FileState::from($attributes['file']['state']),
        );
    }

    public function toArray(): array
    {
        return [
            'file' => [
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
            ],
        ];
    }
}