<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;


class UserRepository extends CoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getUsersByFormData(array $formData, bool $qbOnly = true)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u');
        $qb->from(User::class, 'u');

        if (!empty($formData['username'])) {
            $qb->andWhere($qb->expr()->like('u.username', ':username'));
            $qb->setParameter('username', '%'.$formData['username'].'%');
        }

        if (!empty($formData['createDateFrom'])) {
            $qb->andWhere($qb->expr()->gte('u.createdAt', ':createDateFrom'));
            $dateFromDateTime = new \DateTime($formData['createDateFrom']);
            $qb->setParameter('createDateFrom', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        if (!empty($formData['createDateTo'])) {
            $qb->andWhere($qb->expr()->lte('u.createdAt', ':createDateTo'));
            $dateFromDateTime = new \DateTime($formData['createDateTo']);
            $qb->setParameter('createDateTo', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        if (!empty($formData['lastLoginDateFrom'])) {
            $qb->andWhere($qb->expr()->gte('u.lastLoginDate', ':lastLoginDateFrom'));
            $dateFromDateTime = new \DateTime($formData['lastLoginDateFrom']);
            $qb->setParameter('lastLoginDateFrom', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        if (!empty($formData['lastLoginDateTo'])) {
            $qb->andWhere($qb->expr()->lte('u.lastLoginDate', ':lastLoginDateTo'));
            $dateFromDateTime = new \DateTime($formData['lastLoginDateTo']);
            $qb->setParameter('lastLoginDateTo', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        $qb->orderBy('u.createdAt', 'DESC');

        if ($qbOnly) {
            return $qb;
        }

        return $qb->getQuery()->getResult();
    }
}
