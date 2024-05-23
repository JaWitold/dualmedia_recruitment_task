<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderStatusEnum: string
{
    case PENDING = "pending";
    case CANCELED = "canceled";
    case COMPLETED = "completed";
}
