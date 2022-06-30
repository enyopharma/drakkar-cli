<?php

declare(strict_types=1);

namespace App\Actions;

enum StoreRunResultType
{
    case Success;
    case NoPmid;
    case RunAlreadyExists;
    case NoNewPmid;
}
