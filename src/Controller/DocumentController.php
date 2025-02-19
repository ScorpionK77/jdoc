<?php

namespace App\Controller;

use App\Doctrine\Types\DocumentState;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Document;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/api/document', name: 'api.document.')]
class DocumentController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'view', methods: ['GET'])]
    public function viewAction(Request $request): mixed
    {
        $dao = $this->entityManager->getRepository(Document::class);
        $qb = $dao->createQueryBuilder('p');
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 20);


        $offset = max($page - 1, 0) * $perPage;
        $qb->setMaxResults($perPage)->setFirstResult($offset);

        $token = $this->container->get('security.token_storage')->getToken();
        if ($token instanceof TokenInterface)
        {
            $user = $token->getUser();
        } else
        {
            $user = null;
        }

        $expr = $qb->expr();
        $par = $qb->createNamedParameter(DocumentState::STATUS_PUBLISHED);
        $qb->Where($expr->eq('p.state', $par));

        if ($user instanceof UserInterface)
        {
            $expr = $qb->expr();
            $par = $qb->createNamedParameter($user->getIuserid());
            $qb->orWhere($expr->eq('p.user', $par));
        }

        // сортировка
        $expr = $qb->expr();
        $qb->addOrderBy($expr->asc('p.createAt'));

        // листинг страниц
        $paginator = new Paginator($qb);
        $paginator->setUseOutputWalkers(false);
        $total = $paginator->count();
        $documents = $qb->getQuery()->execute();

        return $this->json([
            'document' => $documents,
            'pagination' => [
                'page'    => $page,
                'perPage' => $perPage,
                'total'   => $total,
            ]
        ]);
    }

    #[Route('/{id}', name: 'id', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function idAction(int $id): mixed
    {
        $dao = $this->entityManager->getRepository(Document::class);
        $document = $dao->find($id);

        if (empty($document))
        {
            throw new NotFoundHttpException('Документ не найден');
        }

        $this->denyAccessUnlessGranted('view', $document, 'Вы не можете просмастривать этот документ');

        return $this->json(['document' => $document]);
    }

    #[Route('', name: 'add', methods: ['POST'])]
    public function addAction(): mixed
    {
        $document = new Document();
        $this->denyAccessUnlessGranted('add', $document, 'Вы не можете создавать документы');

        $token = $this->container->get('security.token_storage')->getToken();
        //dd($token);

        $user = $token->getUser();
        $document->setUser($user);

        // сохраним в базе, и достанем, чтобы получить идентификатор
        if (empty($document->getIdocid()))
        {
            $this->entityManager->persist($document);
        }
        $this->entityManager->flush();

        //dd($document);

        return $this->json(['document' => $document]);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): mixed
    {
        $dao = $this->entityManager->getRepository(Document::class);
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
        $document->setModifyAt(new \DateTime('now'));

        $this->entityManager->flush();

        //dd($document);

        return $this->json(['document' => $document]);
    }
    #[Route('/{id}/publish', name: 'publish', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function publishAction(int $id): mixed
    {
        $dao = $this->entityManager->getRepository(Document::class);
        $document = $dao->find($id);

        if (empty($document))
        {
            throw new NotFoundHttpException('Документ не найден');
        }

        $this->denyAccessUnlessGranted('edit', $document, 'У Вас нет доступа на изменение этого документа');

        if ($document->getState() == 'draft')
        {
            $document->setState('published');
            $document->setModifyAt(new \DateTime('now'));
            $this->entityManager->flush();
        }
        return $this->json(['document' => $document]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): mixed
    {
        $dao = $this->entityManager->getRepository(Document::class);
        $document = $dao->find($id);

        if (empty($document))
        {
            throw new NotFoundHttpException('Документ не найден');
        }

        $this->denyAccessUnlessGranted('delete', $document, 'У Вас нет доступа на удаление этого документа');

        $this->entityManager->remove($document);
        $this->entityManager->flush();

        return $this->json(['succes' => true]);
    }
}