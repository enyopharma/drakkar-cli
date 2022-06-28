<?php

declare(strict_types=1);

namespace App\Actions;

enum PopulateRunResultType
{
    case Success;
    case NotFound;
    case AlreadyPopulated;
    case Failure;
}
