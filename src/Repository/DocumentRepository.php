<?php

namespace App\Repository;

use App\Doctrine\Types\DocumentState;
use App\Entity\Document;
use App\Model\DocumentListResponse;
use App\Model\PaginationResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function getDocuments(UserInterface|null $user, int $page = 1, int $perPage = 10): DocumentListResponse
    {
        $offset = max($page - 1, 0) * $perPage;

        $qb = $this->createQueryBuilder('p');
        $qb->setMaxResults($perPage)->setFirstResult($offset);

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

        $pr = new PaginationResponse($page, $perPage, $total);

        return new DocumentListResponse($documents, $pr);
    }
}
