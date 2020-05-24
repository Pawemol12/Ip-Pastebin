<?php

namespace App\Repository;

use App\Entity\Paste;
use Doctrine\Persistence\ManagerRegistry;

class PasteRepository extends CoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paste::class);
    }

    public function getUserPastesByFormData(array $formData, int $userId, bool $qbOnly = true)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('p');
        $qb->from(Paste::class, 'p');

        $qb->join('p.user', 'u');
        $qb->where($qb->expr()->eq('u.id', ':userId'));

        if (!empty($formData['title'])) {
            $qb->andWhere($qb->expr()->like('p.title', ':title'));
            $qb->setParameter('title', '%'.$formData['title'].'%');
        }

        if (!empty($formData['code'])) {
            $qb->andWhere($qb->expr()->like('p.code', ':code'));
            $qb->setParameter('code', '%'.$formData['code'].'%');
        }

        if (!empty($formData['createDateFrom'])) {
            $qb->andWhere($qb->expr()->gte('p.createDate', ':createDateFrom'));
            $dateFromDateTime = new \DateTime($formData['createDateFrom']);
            $qb->setParameter('createDateFrom', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        if (!empty($formData['createDateTo'])) {
            $qb->andWhere($qb->expr()->lte('p.createDate', ':createDateTo'));
            $dateFromDateTime = new \DateTime($formData['createDateTo']);
            $qb->setParameter('createDateTo', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        if (!empty($formData['expireDateFrom'])) {
            $qb->andWhere($qb->expr()->gte('p.expireDate', ':expireDateFrom'));
            $dateFromDateTime = new \DateTime($formData['expireDateFrom']);
            $qb->setParameter('expireDateFrom', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        if (!empty($formData['expireDateTo'])) {
            $qb->andWhere($qb->expr()->lte('p.expireDate', ':expireDateTo'));
            $dateFromDateTime = new \DateTime($formData['expireDateTo']);
            $qb->setParameter('expireDateTo', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        $qb->orderBy('p.createDate', 'DESC');

        $qb->setParameter('userId', $userId);

        if ($qbOnly) {
            return $qb;
        }

        return $qb->getQuery()->getResult();
    }

    public function getPastesByFormData(array $formData, bool $qbOnly = true)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('p');
        $qb->from(Paste::class, 'p');

        $qb->leftJoin('p.user', 'u');

        if (!empty($formData['username'])) {
            $qb->andWhere($qb->expr()->like('u.username', ':username'));
            $qb->setParameter('username', '%'.$formData['username'].'%');
        }

        if (!empty($formData['title'])) {
            $qb->andWhere($qb->expr()->like('p.title', ':title'));
            $qb->setParameter('title', '%'.$formData['title'].'%');
        }

        if (!empty($formData['code'])) {
            $qb->andWhere($qb->expr()->like('p.code', ':code'));
            $qb->setParameter('code', '%'.$formData['code'].'%');
        }

        if (!empty($formData['createDateFrom'])) {
            $qb->andWhere($qb->expr()->gte('p.createDate', ':createDateFrom'));
            $dateFromDateTime = new \DateTime($formData['createDateFrom']);
            $qb->setParameter('createDateFrom', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        if (!empty($formData['createDateTo'])) {
            $qb->andWhere($qb->expr()->lte('p.createDate', ':createDateTo'));
            $dateFromDateTime = new \DateTime($formData['createDateTo']);
            $qb->setParameter('createDateTo', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        if (!empty($formData['expireDateFrom'])) {
            $qb->andWhere($qb->expr()->gte('p.expireDate', ':expireDateFrom'));
            $dateFromDateTime = new \DateTime($formData['expireDateFrom']);
            $qb->setParameter('expireDateFrom', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        if (!empty($formData['expireDateTo'])) {
            $qb->andWhere($qb->expr()->lte('p.expireDate', ':expireDateTo'));
            $dateFromDateTime = new \DateTime($formData['expireDateTo']);
            $qb->setParameter('expireDateTo', $dateFromDateTime->format('Y-m-d H:i:s'));
        }

        $qb->orderBy('p.createDate', 'DESC');

        if ($qbOnly) {
            return $qb;
        }

        return $qb->getQuery()->getResult();
    }

    public function getExpiredPastes()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('p');
        $qb->from(Paste::class, 'p');

        $qb->where($qb->expr()->lte('p.expireDate', ':expireDateNow'));
        $dateTimeNow = new \DateTime();
        $qb->setParameter('expireDateNow', $dateTimeNow->format('Y-m-d H:i:s'));

        return $qb->getQuery()->getResult();
    }
}
