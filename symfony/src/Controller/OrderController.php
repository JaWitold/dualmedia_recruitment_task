<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Model\OrderModel;
use App\Repository\OrderRepository;
use App\Service\OrderCalculation\ConstVatSumService;
use App\Service\OrderCalculation\NetSumService;
use App\Service\OrderCalculation\OrderCalculationCollectorService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag("Order")]
#[Route('/api/order', name: 'api_order_')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly OrderCalculationCollectorService $collectorService
    ) {
    }

    #[Route('', name: 'index', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/order',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "OK",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: Order::class, groups: ['order_list'])
                    )
                )
            )
        ]
    )]
    public function index(OrderRepository $orderRepository): Response
    {
        return new JsonResponse(
            $this->serializer->serialize(
                $orderRepository->findAll(),
                JsonEncoder::FORMAT,
                ["groups" => ['order_list']]
            ),
            json: true
        );
    }

    #[Route('/{id}', name: 'show', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/order/{id}',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "OK",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'order', ref: new Model(type: Order::class, groups: ['order_show'])),
                        new OA\Property(property: 'totalSum', type: 'string'),
                        new OA\Property(property: 'netSum', type: 'string'),
                        new OA\Property(property: 'vatSum', type: 'string'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Not Found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "type",
                            type: "string",
                            example: "https://symfony.com/doc/current/validation.html"
                        ),
                        new OA\Property(property: "title", type: "string", example: "Validation Failed"),
                        new OA\Property(property: "status", type: "integer", example: Response::HTTP_NOT_FOUND),
                        new OA\Property(
                            property: "detail",
                            type: "string",
                            example: "\"App\\Entity\\Order\" object not found by " .
                            "\"Symfony\\Bridge\\Doctrine\\ArgumentResolver\\EntityValueResolver\"."
                        ),
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function show(
        Order $order,
        NetSumService $itemSumService,
        ConstVatSumService $vatSum
    ): Response {
        return new JsonResponse(
            $this->serializer->serialize(
                new readonly class (
                    $order,
                    number_format($this->collectorService->calculate($order), 2, '.', ' '),
                    number_format($itemSumService->calculate($order), 2, '.', ' '),
                    number_format($vatSum->calculate($order, $itemSumService->calculate($order)), 2, '.', ' ')
                ) {
                    public function __construct(
                        #[Groups(['order_show'])] public Order $order,
                        #[Groups(['order_show'])] public string $totalSum,
                        #[Groups(['order_show'])] public string $netSum,
                        #[Groups(['order_show'])] public string $constVatSum,
                    ) {
                    }
                },
                JsonEncoder::FORMAT,
                ["groups" => ['order_show']]
            ),
            json: true
        );
    }

    #[Route('', name: 'store', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/order',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: OrderModel::class)
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "OK",
                content: new OA\JsonContent(
                    ref: new Model(type: Order::class, groups: ['order_store'])
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation Failed",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "type",
                            type: "string",
                            example: "https://symfony.com/doc/current/validation.html"
                        ),
                        new OA\Property(property: "title", type: "string", example: "Validation Failed"),
                        new OA\Property(
                            property: "status",
                            type: "integer",
                            example: Response::HTTP_UNPROCESSABLE_ENTITY
                        ),
                        new OA\Property(
                            property: "detail",
                            type: "string",
                            example: "This value should be of type int."
                        ),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: "Bad Request",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "type",
                            type: "string",
                            example: "https://symfony.com/doc/current/validation.html"
                        ),
                        new OA\Property(property: "title", type: "string", example: "Validation Failed"),
                        new OA\Property(property: "status", type: "integer", example: Response::HTTP_BAD_REQUEST),
                        new OA\Property(
                            property: "detail",
                            type: "string",
                            example: "Request payload contains invalid \"json\" data."
                        ),
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function store(
        #[MapRequestPayload] OrderModel $orderModel,
        EntityManagerInterface $entityManager
    ): Response {
        $order = new Order();
        foreach ($orderModel->orderItems as $item) {
            $orderItem = new OrderItem();
            $orderItem->setProduct($entityManager->getRepository(Product::class)->find($item->productId));
            $orderItem->setNumber($item->number);
            $order->addOrderItem($orderItem);
            $entityManager->persist($orderItem);
        }
        $order->setSum(
            number_format($this->collectorService->calculate($order), 2, '.', ' '),
        );
        $entityManager->persist($order);
        $entityManager->flush();
        return new JsonResponse($this->serializer->serialize($order, JsonEncoder::FORMAT, [
            "groups" =>
                ['order_store']
        ]), json: true);
    }
}
