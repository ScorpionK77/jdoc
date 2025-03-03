<?php

use App\Tests\AbstractControllerTest;

class UserControllerTest extends AbstractControllerTest
{
    public function testProfile(): void
    {
        $this->client->request('GET', '/api/v1/profile');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'message'
            ],
        ]);

        // отправляем токен не верного формата
        $token = 'eyJpZCI6IjEiLCJyb2xlcyI6WyJST0xFX1VTRVIiXSwiZXhwaXJlcyI6MTc0MTAwOTUxN30.vxFOhItcIWd2sy8O8G-wqkcJYXASGXowai_nllNYEhhKWpX67y5QwvZ2-KLOd9Y3BTQn5xeR1ih6eGXzn-bOPg';
        $customHeaders = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];
        $this->client->setServerParameters($customHeaders);
        $this->assertResponseStatusCodeSame(401);

        // отправляем не валидный токен
        $token = 'eyJpZCI6IjEiLCJyb2x.eyJpZCI6IjEiLCJyb2xlcyI6WyJST0xFX1VTRVIiXSwiZXhwaXJl.vxFOhItcIWd2sy8O8G-wqkcJYXASGXowai_nllNYEhhKWpX67y5QwvZ2-KLOd9';
        $customHeaders = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];
        $this->client->setServerParameters($customHeaders);
        $this->assertResponseStatusCodeSame(401);

        // авторизуемся..
        $this->Auth();

        $this->client->request('GET', '/api/v1/profile');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'iuserid', 'vclogin','roles'
            ],
            'properties' => [
                'iuserid' => ['type' => 'integer'],
                'vclogin' => ['type' => 'string'],
                'roles'   => ['type' => 'array'],
            ],
        ]);
    }
}