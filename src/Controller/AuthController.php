<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use OpenApi\Attributes as OA;

#[Route('/api/v1', name: 'api.')]
class AuthController extends AbstractController
{
    #[Route(path: '/auth', methods: ['POST'])]
    #[OA\Response(response: 200, description: 'Авторизация пользователя.', content: new OA\JsonContent(properties:[
        new OA\Property(property: "user", type: "integer")
    ]))]
    #[OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "username", type: "string", example: 'test'),
            new OA\Property(property: "password", type: "string", example: 'test'),
        ]
    ))]

    public function loginAction(#[CurrentUser] UserInterface $user): JsonResponse
    {
        if (null === $user)
        {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $res = [
            'user'  => $user->getUserIdentifier(),
        ];

        return $this->json($res);
    }
}