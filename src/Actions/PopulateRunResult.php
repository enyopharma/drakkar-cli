<?php

declare(strict_types=1);

namespace App\Actions;

final class PopulateRunResult
{
    public static function success(string $name): self
    {
        return new self(PopulateRunResultType::Success, [$name]);
    }

    public static function notFound(): self
    {
        return new self(PopulateRunResultType::NotFound);
    }

    public static function alreadyPopulated(string $name): self
    {
        return new self(PopulateRunResultType::AlreadyPopulated, [$name]);
    }

    public static function failure(string $name): self
    {
        return new self(PopulateRunResultType::Failure, [$name]);
    }

    /**
     * @param array<mixed> $xs
     */
    private function __construct(
        public readonly PopulateRunResultType $type,
        public readonly array $xs = [],
    ) {
    }
}
