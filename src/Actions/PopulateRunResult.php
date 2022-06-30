<?php

declare(strict_types=1);

namespace App\Actions;

final class PopulateRunResult
{
    public static function success(string $type, string $name): self
    {
        return new self(PopulateRunResultType::Success, [$type, $name]);
    }

    public static function notFound(): self
    {
        return new self(PopulateRunResultType::NotFound);
    }

    public static function alreadyPopulated(string $type, string $name): self
    {
        return new self(PopulateRunResultType::AlreadyPopulated, [$type, $name]);
    }

    public static function failure(string $type, string $name): self
    {
        return new self(PopulateRunResultType::Failure, [$type, $name]);
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
