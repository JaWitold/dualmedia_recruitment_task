<?php

declare(strict_types=1);

namespace App\Service\OrderCalculation;

use App\Entity\Order;

readonly class OrderCalculationCollectorService implements OrderCalculationServiceInterface
{
    /**
     * @param array<OrderCalculationServiceInterface> $orderCalculations
     */
    public function __construct(private array $orderCalculations)
    {
    }

    public function calculate(Order $order, float $price = 0): float
    {
        foreach ($this->orderCalculations as $orderCalculation) {
            $price += $orderCalculation->calculate($order, $price);
        }
        return $price;
    }
}
