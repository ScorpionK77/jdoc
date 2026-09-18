<?php

namespace App\Repository;

use App\Entity\User;
use App\Enum\UserState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param int|null $excludedUserId
     * @return User[]
     */
    public function findApproved(?int $excludedUserId = null): array
    {
        $builder = $this->createQueryBuilder('u')
            ->andWhere('u.istateid = :state')
            ->setParameter('state', UserState::APPROVED);

        if ($excludedUserId)
        {
            $builder->andWhere('u.iuserid <> :excludedId')
                ->setParameter('excludedId', $excludedUserId);
        }

        return $builder->getQuery()->getResult();
    }
}
