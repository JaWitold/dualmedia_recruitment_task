<?php

declare(strict_types=1);

namespace App\Model;

use App\Validator as CustomAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(title: "OrderItem")]
class OrderItemModel
{
    #[Assert\Positive]
    #[CustomAssert\ExistingProductId]
    #[Assert\Type(
        type: 'integer',
        message: 'The value {{ value }} is not a valid {{ type }}.',
    )]
    public int $productId;

    #[Assert\Positive]
    #[Assert\Type(
        type: 'integer',
        message: 'The value {{ value }} is not a valid {{ type }}.',
    )]
    public int $number;
}
