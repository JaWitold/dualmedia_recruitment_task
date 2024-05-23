<?php

declare(strict_types=1);

namespace App\Service\OrderCalculation;

use App\Entity\Order;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: 'app.item_sum_service', public: true)]
class NetSumService implements OrderCalculationServiceInterface
{
    public function calculate(Order $order, float $price = 0): float
    {
        foreach ($order->getOrderItems() as $orderItem) {
            $price += (float)$orderItem->getProduct()?->getPrice() * ($orderItem->getNumber() ?? 0);
        }
        return $price;
    }
}
