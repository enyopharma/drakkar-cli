<?php

declare(strict_types=1);

namespace App\Actions;

interface PopulatePublicationInterface
{
    public function populate(int $pmid): PopulatePublicationResult;
}
