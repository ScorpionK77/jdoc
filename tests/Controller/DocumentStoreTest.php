<?php
namespace App\Tests\Controller;

use App\Tests\AbstractControllerTest;
use App\Repository\UserRepository;
use App\Services\JWT\JWTManager;
use PHPUnit\Framework\Attributes\Depends;

class DocumentStoreTest extends AbstractControllerTest
{
    protected function getDocumentShema()
    {
        return [
            'type' => 'object',
            'required' => [
                'document'
            ],
            'properties' => [
                'document' => [
                    'type'       => 'object',
                    'required'   => ['idocid', 'state','createAt','modifyAt'],
                    'properties' => [
                        'idocid'   => ['type' => 'integer'],
                        'state'    => ['type' => 'string', 'enum' => ['draft','published']],
                        'createAt' => ['type' => 'string'],
                        'modifyAt' => ['type' => 'string'],
                    ],
                ],
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        // авторизовываем пользователя
        $this->Auth();
    }

    public function testAddDocument(): int
    {
        $this->client->request('POST', '/api/v1/document');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, $this->getDocumentShema());

        return $responseContent['document']['idocid'];
    }

    #[Depends('testAddDocument')]
    public function testEditDocument($docId): int
    {
        $payload = [
            'document' => [
                'payload' => [
                    'pkey'      => 'idocid',
                    'pageSize'  =>	100,
                    'perm' => [
                        'canAdd' => false,
                        'canEdit' => false,
                        'canDelete' => false,
                    ]
                ]
            ]
        ];

        $this->client->request('PUT', '/api/v1/document/' . $docId, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, $this->getDocumentShema());

        // пытаемся отправить пустое тело документа
        $payload = [
            'document' => [
            ]
        ];
        $this->client->request('PUT', '/api/v1/document/' . $docId, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'code', 'message'
            ],
        ]);
        return $docId;
    }

    #[Depends('testEditDocument')]
    public function testPublishDocument($docId): int
    {
        $this->client->request('POST', '/api/v1/document/' . $docId . '/publish');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, $this->getDocumentShema());

        $this->assertEquals('published', $responseContent['document']['state'], 'Документ не в том статусе');

        // публикация не существующего документа
        $this->client->request('POST', '/api/v1/document/9999/publish');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'code', 'message'
            ],
        ]);
        return $docId;
    }

    #[Depends('testPublishDocument')]
    public function testEditPublishDocument($docId): int
    {
        // нельзя редактировать опубликованный документ
        $payload = [
            'document' => [
                'payload' => [
                    'pkey'      => 'idocid',
                    'pageSize'  =>	100,
                    'perm' => [
                        'canAdd' => false,
                        'canEdit' => false,
                        'canDelete' => false,
                    ]
                ]
            ]
        ];

        $this->client->request('PUT', '/api/v1/document/' . $docId, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'code', 'message'
            ],
        ]);

        return $docId;
    }

    #[Depends('testEditPublishDocument')]
    public function testDeleteDocument($docId): void
    {
        $this->client->request('DELETE', '/api/v1/document/' . $docId);
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'succes'
            ],
        ]);

        $this->assertEquals(true, $responseContent['succes'], 'Документ не удален');

        // повторное удаление приведет к ошибке не найдено
        $this->client->request('DELETE', '/api/v1/document/' . $docId);
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'code', 'message'
            ],
        ]);
    }
}