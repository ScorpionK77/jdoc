<?php

namespace App\Controller\Document;

use App\Entity\Document;
use App\Model\ErrorResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/v1/document/{id}', name: 'api.document.edit', methods: ['PUT'], requirements: ['id' => '\d+'])]
#[OA\Response(response: 200, description: 'Редактирование документа по идентификатору.', content: new Model(type: Document::class))]
#[OA\Response(response: 404, description: 'Документ не найден.', content: new Model(type: ErrorResponse::class))]
#[OA\Response(response: 401, description: 'Нет прав доступа на редактирование.', content: new Model(type: ErrorResponse::class))]
#[OA\PathParameter(name: 'id', description: 'Идентификатор документа', required: true)]
#[OA\RequestBody(content: new OA\JsonContent(
    properties: [
        new OA\Property(property: "document", type: "object", properties: [
            new OA\Property(property: "payload", type: "object")
        ]),
    ]
))]
class EditAction extends AbstractController
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $document = new Document();
        $this->denyAccessUnlessGranted('add', $document, 'Вы не можете создавать документы');

        $token = $this->container->get('security.token_storage')->getToken();
        $user = $token->getUser();
        $document->setUser($user);

        // сохраним в базе, и достанем, чтобы получить идентификатор
        $entityManager->persist($document);
        $entityManager->flush();

        return $this->json(['document' => $document]);
    }
}