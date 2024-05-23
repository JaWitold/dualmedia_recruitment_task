<?php

declare(strict_types=1);

namespace App\Service\OrderCalculation;

use App\Entity\Order;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: 'app.vat_sum_service', public: true)]
class ConstVatSumService implements OrderCalculationServiceInterface
{
    private const float VAT_RATE = 0.23;

    public function calculate(Order $order, float $price = 0): float
    {
        return $price * self::VAT_RATE;
    }
}
