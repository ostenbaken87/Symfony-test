<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiAuthSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Проверяем только маршруты, начинающиеся с /api/
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        // Проверяем наличие заголовка X-API-User-Name
        $apiUserName = $request->headers->get('X-API-User-Name');

        if ($apiUserName !== 'admin') {
            $response = new JsonResponse([
                'success' => false,
                'error' => 'Access denied. X-API-User-Name header must be "admin"',
            ], Response::HTTP_FORBIDDEN);

            $event->setResponse($response);
        }
    }
}

