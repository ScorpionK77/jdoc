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
                'document', 'pagination'
            ],
            'properties' => [
                'document' => [
                    'type'       => 'array',
                    'required'   => ['idocid', 'state','createAt','modifyAt'],
                    'properties' => [
                        'idocid'   => ['type' => 'integer'],
                        'state'    => ['type' => 'string', 'enum' => ['draft','published']],
                        'createAt' => ['type' => 'string'],
                        'modifyAt' => ['type' => 'string'],
                    ],
                ],
                'pagination' => [
                    'type'       => 'object',
                    'required'   => ['page', 'perPage','total'],
                    'properties' => [
                        'page'    => ['type' => 'integer'],
                        'perPage' => ['type' => 'integer'],
                        'total'   => ['type' => 'integer']
                    ],
                ]
            ]
        ]);
    }
}