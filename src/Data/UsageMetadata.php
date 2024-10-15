<?php

namespace Gemini\Data;

use Gemini\Contracts\Arrayable;

class UsageMetadata implements Arrayable
{
    public function __construct(
        public readonly int $promptTokenCount,
        public readonly int $candidatesTokenCount,
        public readonly int $totalTokenCount,
    )
    {
    }

    /**
     * @param array{ promptTokenCount: int, candidatesTokenCount: int, totalTokenCount: int } $attributes
     */
    public static function from(array $attributes): self
    {
        return new self(
            promptTokenCount    : $attributes['promptTokenCount'],
            candidatesTokenCount: $attributes['candidatesTokenCount'],
            totalTokenCount     : $attributes['totalTokenCount'],
        );
    }

    public function toArray(): array
    {
        return [
            'promptTokenCount'     => $this->promptTokenCount,
            'candidatesTokenCount' => $this->candidatesTokenCount,
            'totalTokenCount'      => $this->totalTokenCount,
        ];
    }
}