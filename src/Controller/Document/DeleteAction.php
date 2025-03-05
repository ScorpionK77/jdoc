<?php

namespace App\Controller\Document;

use App\Entity\Document;
use App\Model\ErrorResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/v1/document/{id}', name: 'api.document.delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
#[OA\Response(response: 200, description: 'Удаление документа по идентификатору.', content: new Model(type: Document::class))]
#[OA\Response(response: 404, description: 'Документ не найден.', content: new Model(type: ErrorResponse::class))]
#[OA\Response(response: 401, description: 'Нет прав доступа на удаление.', content: new Model(type: ErrorResponse::class))]
#[OA\PathParameter(name: 'id', description: 'Идентификатор документа', required: true)]
class DeleteAction extends AbstractController
{
    public function __invoke(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $dao = $entityManager->getRepository(Document::class);
        $document = $dao->find($id);

        if (empty($document))
        {
            throw new NotFoundHttpException('Документ не найден');
        }

        $this->denyAccessUnlessGranted('delete', $document, 'У Вас нет доступа на удаление этого документа');

        $entityManager->beginTransaction();
        try {
            $entityManager->remove($document);
            $entityManager->flush();
            $entityManager->commit();
        } catch (\Exception  $e) {
            $entityManager->rollback();
            throw new BadRequestHttpException('Не удалось удалить документ', $e);
        }
        return $this->json(['succes' => true]);
    }
}