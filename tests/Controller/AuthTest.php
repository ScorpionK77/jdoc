<?php
namespace App\Tests\Controller;

use App\Tests\AbstractControllerTest;
use PHPUnit\Framework\Attributes\DataProvider;

class AuthTest extends AbstractControllerTest
{
    /**
     * Тестируем авторизацию
     *
     * @return void
     */
    #[DataProvider('providerUsers')]
    public function testAuth(array $user, int $code, array $schema): void
    {
        $this->client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($user));
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        if ($code == 200)
        {
            $this->assertResponseIsSuccessful();
        } else
        {
            $this->assertResponseStatusCodeSame($code);
        }
        $this->assertJsonDocumentMatchesSchema($responseContent, $schema);
    }

    public static function providerUsers(): \Generator
    {
        yield [
            [
                'username' => 'notfound',
                'password' => 'test',
            ],
            401,
            [
                'type' => 'object',
                'required' => [
                    'error'
                ],
                'properties' => [
                    'error'   => ['type' => 'string']
                ]
            ]
        ];
        yield [
            [
                'username' => 'test',
                'password' => 'test',
            ],
            200,
            [
                'type' => 'object',
                'required' => [
                    'token'
                ],
                'properties' => [
                    'token'   => ['type' => 'string']
                ]
            ]
        ];
    }
}