<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Helmich\JsonAssert\JsonAssertions;
class DocumentControllerTest extends WebTestCase
{
    use JsonAssertions;

    public function testDocumentById(): void
    {
        $client = $this->createClient();

        $docId = 1;

        $client->request('GET', '/api/document/'.$docId);
        $responseContent = json_decode($client->getResponse()->getContent(), true);

        //dd($responseContent);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => [
                'document'
            ],
            'properties' => [
                'document' => [
                    'type' => 'object',
                    'required' => ['idocid', 'iuserid', 'state','createAt','modifyAt'],
                    'properties' => [
                        'idocid' => ['type' => 'integer'],
                        'iuserid' => ['type' => 'integer'],
                        'state' => ['type' => 'string', 'enum' => ['draft','published']],
                        'createAt' => ['type' => 'string'],
                        'modifyAt' => ['type' => 'string'],
                    ],
                ],
            ],
        ]);
    }

    public function testAddDocument(): void
    {
        $client = $this->createClient();

        $client->request('POST', '/api/document');
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        dd($responseContent);

        $this->assertResponseIsSuccessful();
    }
}