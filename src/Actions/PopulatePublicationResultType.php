<?php

declare(strict_types=1);

namespace App\Actions;

enum PopulatePublicationResultType
{
    case Success;
    case NotFound;
    case AlreadyPopulated;
    case ParsingError;
}
