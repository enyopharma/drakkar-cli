<?php

declare(strict_types=1);

namespace App\Actions;

final class StoreRunResult
{
    public static function success(int $id): self
    {
        return new self(StoreRunResultType::Success, [$id]);
    }

    public static function noPmid(): self
    {
        return new self(StoreRunResultType::NoPmid);
    }

    public static function runAlreadyExists(int $id): self
    {
        return new self(StoreRunResultType::RunAlreadyExists, [$id]);
    }

    public static function noNewPmid(): self
    {
        return new self(StoreRunResultType::NoNewPmid);
    }

    /**
     * @param array<mixed> $xs
     */
    private function __construct(
        public readonly StoreRunResultType $type,
        public readonly array $xs = [],
    ) {
    }

    public function id(): int
    {
        if ($this->type === StoreRunResultType::Success) {
            return $this->xs[0];
        }

        throw new \LogicException('Result has no id');
    }
}
