<?php

namespace App\Controller;

use App\Jwt\TokenManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityController extends RESTController
{
    /**
     * @Route("/login", name="login")
     * @param TokenManager $tokenManager
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function login(TokenManager $tokenManager)
    {
        /** @var UserInterface $user */
        $user = $this->getUser();

        return $this->json([
            'user'  => $user->getUsername(),
            'token' => $tokenManager->generateToken($user->getUsername()),
        ]);
    }
}
