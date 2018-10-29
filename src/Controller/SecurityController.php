<?php

namespace App\Controller;

use App\Jwt\TokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityController extends RESTController
{
    /**
     * @Route("/login", name="login", methods={"POST"})
     * @param TokenManagerInterface $tokenManager
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function login(TokenManagerInterface $tokenManager): JsonResponse
    {
        /** @var UserInterface $user */
        $user = $this->getUser();

        return $this->json([
            'user'  => $user->getUsername(),
            'token' => $tokenManager->generateToken($user->getUsername()),
        ]);
    }
}
