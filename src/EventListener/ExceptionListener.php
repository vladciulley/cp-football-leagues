<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    /** @var ContainerInterface */
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        
        if ($exception instanceof HttpExceptionInterface) {

            switch ($exception->getStatusCode()) {

                case JsonResponse::HTTP_NOT_FOUND:
                    $response = new JsonResponse(['message' => 'Resource Not Found'], $exception->getStatusCode());
                    $response->headers->set('Content-Type', 'application/json');
                    break;

                default:
                    // do nothing
            }

            if (isset($response)) {
                $event->setResponse($response);
            }
        }
    }
}