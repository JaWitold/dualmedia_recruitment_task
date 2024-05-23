<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ExistingProductId extends Constraint
{
    public string $message = 'The product with ID "{{ productId }}" does not exist.';

    public function validatedBy(): string
    {
        return static::class . 'Validator';
    }
}
