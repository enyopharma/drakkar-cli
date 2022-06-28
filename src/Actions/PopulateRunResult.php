<?php

declare(strict_types=1);

namespace App\Actions;

final class PopulateRunResult
{
    const SUCCESS = 0;
    const NOT_FOUND = 1;
    const ALREADY_POPULATED = 2;
    const FAILURE = 3;

    public static function success(string $name): self
    {
        return new self(self::SUCCESS, $name);
    }

    public static function notFound(): self
    {
        return new self(self::NOT_FOUND);
    }

    public static function alreadyPopulated(string $name): self
    {
        return new self(self::ALREADY_POPULATED, $name);
    }

    public static function failure(string $name): self
    {
        return new self(self::FAILURE, $name);
    }

    /**
     * @param 0|1|2|3 $status
     */
    private function __construct(
        private int $status,
        private string $name = '',
    ) {
    }

    /**
     * @return 0|1|2|3
     */
    public function status()
    {
        return $this->status;
    }

    public function name(): string
    {
        $types = [self::SUCCESS, self::ALREADY_POPULATED, self::FAILURE];

        if (in_array($this->status, $types, true)) {
            return $this->name;
        }

        throw new \LogicException('Result has no name');
    }
}
