<?php

namespace App\Controller;

use App\Entity\User;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use OpenApi\Attributes as OA;

#[Route('/api/v1', name: 'api.')]
class UserController extends AbstractController
{
    #[Route(path: '/profile', name: 'profile',  methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Данные пользователя.', content: new Model(type: User::class))]
    public function profileAction(#[CurrentUser] ?UserInterface $user): JsonResponse
    {
        //dd($user);

        //$token = $this->container->get('security.token_storage')->getToken();
        //dd($token);
        if (null === $user)
        {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($user);
    }
}