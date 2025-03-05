<?php

namespace App\Controller\Document;

use App\Entity\Document;
use App\Model\ErrorResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
    public function __invoke(EntityManagerInterface $entityManager, Request $request, int $id): JsonResponse
    {
        $dao = $entityManager->getRepository(Document::class);
        $document = $dao->find($id);

        if (empty($document))
        {
            throw new NotFoundHttpException('Документ не найден');
        }
        $this->denyAccessUnlessGranted('edit', $document, 'У Вас нет доступа на изменение этого документа');

        if ($document->getState() == 'published')
        {
            throw new BadRequestHttpException('Документ опубликован');
        }

        $content = json_decode($request->getContent(), true);
        if (empty($content['document']['payload']))
        {
            throw new BadRequestHttpException('Не задано тело документа');
        }
        $document->setPayload($content['document']['payload']);
        $entityManager->beginTransaction();
        try {
            $entityManager->flush();
            $entityManager->commit();
        } catch (\Exception  $e) {
            $entityManager->rollback();
            throw new BadRequestHttpException('Не удалось отредактировать документ', $e);
        }

        return $this->json(['document' => $document]);
    }
}