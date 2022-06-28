<?php

declare(strict_types=1);

namespace App\Actions;

interface PopulateRunInterface
{
    public function populate(int $id, callable $populate): PopulateRunResult;
}
