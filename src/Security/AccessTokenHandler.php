<?php

namespace App\Security;

use App\Services\JWT\JWTManager;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private JWTManager $jwtManager)
    {
    }
    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        $payload = $this->jwtManager->parse($accessToken);
        return new UserBadge($payload['id'], null, $payload);
    }
}