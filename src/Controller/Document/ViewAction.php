<?php

namespace App\Controller\Document;

use App\Entity\Document;
use App\Model\DocumentListResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[Route('/api/v1/document', name: 'api.document.view', methods: ['GET'])]
#[OA\Response(response: 200, description: 'Возвращает список опубликованных документов.', content: new Model(type: DocumentListResponse::class))]
#[OA\QueryParameter(name: 'page', description: 'Номер страницы')]
#[OA\QueryParameter(name: 'perPage', description: 'Количество документов на страницу')]
class ViewAction extends AbstractController
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $dao = $entityManager->getRepository(Document::class);

        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 20);

        $token = $this->container->get('security.token_storage')->getToken();
        if ($token instanceof TokenInterface)
        {
            $user = $token->getUser();
        } else
        {
            $user = null;
        }

        $result = $dao->getDocuments($user, $page, $perPage);
        return $this->json($result);
    }
}