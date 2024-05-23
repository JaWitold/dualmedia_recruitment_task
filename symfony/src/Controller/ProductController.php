<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag("Product")]
#[Route('/api/product', name: 'api_product_')]
class ProductController extends AbstractController
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    #[Route('', name: 'index', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/product',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "OK",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Product::class, groups: ['product_list']))
                )
            )
        ]
    )]
    public function index(ProductRepository $productRepository): Response
    {
        return new JsonResponse(
            $this->serializer->serialize(
                $productRepository->findAll(),
                JsonEncoder::FORMAT,
                ["groups" => ['product_list']]
            ),
            json: true
        );
    }

    #[Route('/{id}', name: 'show', methods: [Request::METHOD_GET])]
    #[OA\Get(
        path: '/api/product/{id}',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "OK",
                content: new OA\JsonContent(
                    ref: new Model(type: Product::class, groups: ['product_show'])
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
                            example: "\"App\\Entity\\Product\" object not found by " .
                            "\"Symfony\\Bridge\\Doctrine\\ArgumentResolver\\EntityValueResolver\"."
                        ),
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function show(Product $product): Response
    {
        return new JsonResponse(
            $this->serializer->serialize(
                $product,
                JsonEncoder::FORMAT,
                ["groups" => ['product_show']]
            ),
            json: true
        );
    }

    #[Route('', name: 'store', methods: [Request::METHOD_POST])]
    #[OA\Post(
        path: '/api/product',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: Product::class, groups: ['product_store'])
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "OK",
                content: new OA\JsonContent(
                    ref: new Model(type: Product::class)
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
                            example: "https://symfony.com/errors/validation"
                        ),
                        new OA\Property(property: "title", type: "string", example: "Validation Failed"),
                        new OA\Property(property: "status", type: "integer", example: 422),
                        new OA\Property(
                            property: "detail",
                            type: "string",
                            example: "This value should be of type int."
                        ),
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function store(
        #[MapRequestPayload] Product $product,
        EntityManagerInterface $entityManager,
    ): Response {
        $entityManager->persist($product);
        $entityManager->flush();
        return new JsonResponse(
            $this->serializer->serialize(
                $product,
                JsonEncoder::FORMAT,
                ['groups' => ['product_store']]
            ),
            json: true
        );
    }
}
