<?php

namespace App\Tests;

use App\Repository\UserRepository;
use App\Services\JWT\JWTManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Helmich\JsonAssert\JsonAssertions;

abstract class AbstractControllerTest extends WebTestCase
{
    use JsonAssertions;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

    }

    protected function Auth()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['vclogin' => 'test']);

        // создаем токен авторизации
        $jwtManager = new JWTManager(getenv('TOKEN_TTL'));
        $token = $jwtManager->create($user);
        $customHeaders = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];
        $this->client->setServerParameters($customHeaders);
    }

}