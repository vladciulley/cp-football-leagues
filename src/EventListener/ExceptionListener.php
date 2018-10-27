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
                    $response = new JsonResponse(['message' => 'Resource Not Found']);
                    break;
                    
                case JsonResponse::HTTP_METHOD_NOT_ALLOWED:
                    $response = new JsonResponse(['message' => 'HTTP Method Not Allowed']);
                    break;
                    
                case JsonResponse::HTTP_INTERNAL_SERVER_ERROR:
                    $response = new JsonResponse(['message' => 'Server Error']);
                    break;
                    
                default:
                    // do nothing
            }

            if (isset($response)) {
                
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->set('Content-Type', 'application/json');
                
                $event->setResponse($response);
            }
        }
    }
}