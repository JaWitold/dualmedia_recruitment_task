<?php

declare(strict_types=1);

namespace App\Service\OrderCalculation;

use App\Entity\Order;

interface OrderCalculationServiceInterface
{
    public function calculate(Order $order, float $price = 0): float;
}
