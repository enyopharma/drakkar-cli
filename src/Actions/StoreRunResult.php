<?php

declare(strict_types=1);

namespace App\Actions;

final class StoreRunResult
{
    const SUCCESS = 0;
    const NO_PMID = 1;
    const RUN_ALREADY_EXISTS = 2;
    const ASSOCIATION_ALREADY_EXISTS = 3;

    public static function success(int $id): self
    {
        return new self(self::SUCCESS, $id);
    }

    public static function noPmid(): self
    {
        return new self(self::NO_PMID);
    }

    public static function runAlreadyExists(int $id, string $name): self
    {
        return new self(self::RUN_ALREADY_EXISTS, $id, $name);
    }

    public static function associationAlreadyExists(int $id, string $name, int $pmid): self
    {
        return new self(self::ASSOCIATION_ALREADY_EXISTS, $id, $name, $pmid);
    }

    /**
     * @param 0|1|2|3 $status
     */
    private function __construct(
        private int $status,
        private ?int $id = null,
        private string $name = '',
        private ?int $pmid = null,
    ) {
    }

    /**
     * @return 0|1|2|3
     */
    public function status()
    {
        return $this->status;
    }

    public function id(): int
    {
        $types = [self::SUCCESS, self::RUN_ALREADY_EXISTS, self::ASSOCIATION_ALREADY_EXISTS];

        if (in_array($this->status, $types, true) && !is_null($this->id)) {
            return $this->id;
        }

        throw new \LogicException('Result has no id');
    }

    public function name(): string
    {
        $types = [self::RUN_ALREADY_EXISTS, self::ASSOCIATION_ALREADY_EXISTS];

        if (in_array($this->status, $types, true)) {
            return $this->name;
        }

        throw new \LogicException('Result has no name');
    }

    public function pmid(): int
    {
        $types = [self::ASSOCIATION_ALREADY_EXISTS];

        if (in_array($this->status, $types, true) && !is_null($this->pmid)) {
            return $this->pmid;
        }

        throw new \LogicException('Result has no pmid');
    }
}
