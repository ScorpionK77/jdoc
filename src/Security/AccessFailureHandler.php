<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class AccessFailureHandler implements AuthenticationFailureHandlerInterface
{

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $errorMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
        return new JsonResponse(['error' => $errorMessage], JsonResponse::HTTP_UNAUTHORIZED);
    }
}