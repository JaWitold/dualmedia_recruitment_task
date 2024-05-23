<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationEventListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationFailedException) {
            $violations = $exception->getViolations();
            $formattedViolations = [];

            foreach ($violations as $violation) {
                $formattedViolations[] = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'title' => $violation->getMessage(),
                ];
            }

            $response = new JsonResponse([
                'type' => 'https://symfony.com/doc/current/validation.html',
                'title' => 'Validation Failed',
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'violations' => $formattedViolations,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

            $event->setResponse($response);
        } elseif ($exception instanceof NotEncodableValueException) {
            $response = new JsonResponse([
                'type' => 'https://symfony.com/doc/current/validation.html',
                'title' => 'Validation Failed',
                'status' => Response::HTTP_BAD_REQUEST,
                'detail' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);

            $event->setResponse($response);
        } elseif ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse([
                'type' => 'https://symfony.com/doc/current/validation.html',
                'title' => 'Validation Failed',
                'status' => $exception->getStatusCode(),
                'detail' => $exception->getMessage(),
            ], $exception->getStatusCode());

            $event->setResponse($response);
        }
    }
}
