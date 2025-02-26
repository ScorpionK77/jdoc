<?php

namespace App\Controller\Document;

use App\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/v1/document', name: 'api.document.add', methods: ['POST'])]
#[OA\Response(response: 200, description: 'Создает новый документ.', content: new Model(type: Document::class))]
#[OA\Response(response: 401, description: 'Нет права доступа на создание.', content: new OA\JsonContent(properties:[
    new OA\Property(property: "code", type: "integer", example: 401),
    new OA\Property(property: "message", type: "string", example: "Текст ошибки")
]))]
class AddAction extends AbstractController
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