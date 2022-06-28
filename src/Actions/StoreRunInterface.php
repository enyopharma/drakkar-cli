<?php

declare(strict_types=1);

namespace App\Actions;

interface StoreRunInterface
{
    public function type(): string;

    public function store(string $name, int ...$pmids): StoreRunResult;
}
