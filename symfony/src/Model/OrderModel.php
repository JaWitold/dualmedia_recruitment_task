<?php

declare(strict_types=1);

namespace App\Model;

use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(title: "Order")]
class OrderModel
{
    /**
     * @var array<OrderItemModel>
     */
    #[OA\Property(
        description: "List of ordered items",
        type: "array",
        items: new OA\Items(ref: new Model(type: OrderItemModel::class))
    )]
    #[Assert\Valid]
    #[Assert\Type(
        type: 'array',
        message: 'The value {{ value }} is not a valid {{ type }}.',
    )]
    public array $orderItems;
}
