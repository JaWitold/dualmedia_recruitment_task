<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;

class ExistingProductIdValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        $product = $this->entityManager->getRepository(Product::class)->find($value);

        if ($product === null) {
            /** @var ExistingProductId $constraint */
            /** @var string $value */
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ productId }}', $value)
                ->addViolation();
        }
    }
}
