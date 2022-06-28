<?php

declare(strict_types=1);

namespace App\Actions;

final class PopulatePublicationResult
{
    const SUCCESS = 0;
    const NOT_FOUND = 1;
    const ALREADY_POPULATED = 2;
    const PARSING_ERROR = 3;

    public static function success(): self
    {
        return new self(self::SUCCESS);
    }

    public static function notFound(): self
    {
        return new self(self::NOT_FOUND);
    }

    public static function alreadyPopulated(): self
    {
        return new self(self::ALREADY_POPULATED);
    }

    public static function parsingError(string $message): self
    {
        return new self(self::PARSING_ERROR, $message);
    }

    /**
     * @param 0|1|2|3 $status
     */
    private function __construct(
        private int $status,
        private string $message = '',
    ) {
    }

    /**
     * @return 0|1|2|3
     */
    public function status()
    {
        return $this->status;
    }

    public function message(): string
    {
        $types = [self::PARSING_ERROR];

        if (in_array($this->status, $types, true)) {
            return $this->message;
        }

        throw new \LogicException('Result has no message');
    }
}
