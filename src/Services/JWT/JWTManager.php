<?php

namespace App\Services\JWT;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class JWTManager
{
    private JWTEncoder $encoder;

    public function __construct(private int $token_ttl)
    {
        $this->encoder = new JWTEncoder();
    }

    /**
     * Создает JWT Token
     *
     * @param UserInterface $user
     * @return string
     */
    public function create(UserInterface $user): string
    {
        //$token_ttl = 3600; // время жизни токена
        // попробуем сгенерить токен
        $payload = [
            'id'       => $user->getUserIdentifier(),
            'roles'    => $user->getRoles(),
            'expires'  => time() + $this->token_ttl
        ];
        return $this->encoder->encode($payload);
    }

    /**
     * Валидация токена и возвращение данных их него
     *
     * @param string $token
     * @return array
     */
    public function parse(string $token): array
    {
        $access = $this->encoder->decode($token);

        if (empty($access))
        {
            throw new BadCredentialsException('Invalid credentials.');
        }

        $payload = $access['payload'] ?? [];

        if (time() > $payload['expires'])
        {
            throw new BadCredentialsException('Invalid credentials.');
        }
        return $payload;
    }
}