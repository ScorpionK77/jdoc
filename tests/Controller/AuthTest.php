<?php
namespace App\Tests\Controller;

use App\Tests\AbstractControllerTest;

class AuthTest extends AbstractControllerTest
{
    /**
     * Тестируем авторизацию
     *
     * @return void
     */
    public function testAuth(): void
    {
        $content = [
            'username' => 'test',
            'password' => 'test',
        ];

        $this->client->request('POST', 'api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($content));
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        //dd($responseContent);
        $this->assertResponseIsSuccessful();
        // проверяем формат выдачи
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'token'
            ],
            'properties' => [
                'token'   => ['type' => 'string']
            ]
        ]);
    }
}