<?php
namespace App\Tests\Controller;

use App\Tests\AbstractControllerTest;
use PHPUnit\Framework\Attributes\Depends;

class DocumentViewTest extends AbstractControllerTest
{
    /**
     * Тестируем выдачу документов без авторизации
     *
     * @return int
     */
    public function testDocuments(): int
    {
        $this->client->request('GET', '/api/v1/document?page=1&perPage=10');
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
        // должно быть 5 штук
        $this->assertCount(5, $responseContent['document'], 'Документов должно быть 5 штук');
        $docId = $responseContent['document'][0]['idocid'];
        // смотрим что после авторизации получается
        $this->Auth();
        $this->client->request('GET', '/api/v1/document?page=1&perPage=10');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        //dd($responseContent);
        $this->assertResponseIsSuccessful();
        // должно быть 10 штук
        $this->assertCount(10, $responseContent['document'], 'Документов должно быть 10 штук');

        // вернем первый элемент
        return $docId;
    }

    #[Depends('testDocuments')]
    public function testDocumentById($docId): int
    {
        $this->client->request('GET', '/api/v1/document/'.$docId);
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        //dd($responseContent);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, [
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
        ]);

        return $docId;
    }

    public function testDocumentNotFound(): void
    {
        $this->client->request('GET', '/api/v1/document/999999');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        //dd($responseContent);

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'code', 'message'
            ],
        ]);
    }
}