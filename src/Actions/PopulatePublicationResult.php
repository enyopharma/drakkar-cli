<?php

declare(strict_types=1);

namespace App\Actions;

final class PopulatePublicationResult
{
    public static function success(): self
    {
        return new self(PopulatePublicationResultType::Success);
    }

    public static function notFound(): self
    {
        return new self(PopulatePublicationResultType::NotFound);
    }

    public static function alreadyPopulated(): self
    {
        return new self(PopulatePublicationResultType::AlreadyPopulated);
    }

    public static function parsingError(string $message): self
    {
        return new self(PopulatePublicationResultType::ParsingError, [$message]);
    }

    /**
     * @param array<mixed> $xs
     */
    private function __construct(
        public readonly PopulatePublicationResultType $type,
        public readonly array $xs = [],
    ) {
    }
}
