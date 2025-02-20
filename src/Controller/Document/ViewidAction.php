<?php

namespace App\Controller\Document;

use App\Entity\Document;
use App\Model\ErrorResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/v1/document/{id}', name: 'api.document.viewid', methods: ['GET'], requirements: ['id' => '\d+'])]
#[OA\Response(response: 200, description: 'Возвращает документ по идентификатору.', content: new Model(type: Document::class))]
#[OA\Response(response: 404, description: 'Документ не найден.', content: new Model(type: ErrorResponse::class))]
#[OA\PathParameter(name: 'id', description: 'Идентификатор документа', required: true)]
class ViewidAction extends AbstractController
{
    public function __invoke(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $dao = $entityManager->getRepository(Document::class);
        $document = $dao->find($id);

        if (empty($document))
        {
            throw new NotFoundHttpException('Документ не найден');
        }

        $this->denyAccessUnlessGranted('view', $document, 'Вы не можете просмастривать этот документ');

        return $this->json(['document' => $document]);
    }
}