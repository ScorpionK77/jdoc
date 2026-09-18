<?php
namespace App\Tests\Controller;

use App\Tests\AbstractControllerTest;
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

    public function testStoreDocument(): void
    {
        $docId = $this->addDocument();

        $this->editDocument($docId);

        $this->publishDocument($docId);

        $this->editPublishDocument($docId);

        $this->deleteDocument($docId);
    }

    public function addDocument(): int
    {
        $this->client->request('POST', '/api/v1/document');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, $this->getDocumentShema());

        return $responseContent['document']['idocid'];
    }

    public function editDocument($docId): int
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

    public function publishDocument($docId): int
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

    public function editPublishDocument($docId): int
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

    public function deleteDocument($docId): void
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