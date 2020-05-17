<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @author Paweł Lodzik <Pawemol12@gmail.com>
 */
abstract class CoreRepository extends ServiceEntityRepository {

    /**
     * @param ManagerRegistry $registry
     * @param string $class
     */
    public function __construct(ManagerRegistry $registry, string $class)
    {
        parent::__construct($registry, $class);
    }

    /**
     * @param mixed $entity
     * @return int
     */
    public function save($entity) {
        $this->persist($entity);
        $this->flush();

        return method_exists($entity, 'getId') ? $entity->getId() : $entity;
    }

    /**
     * @param iterable $entityList
     */
    public function saveList(iterable $entityList) {
        foreach($entityList as $entity){
            $this->persist($entity);
        }
        $this->flush();
    }

    /**
     * @param mixed $entity
     */
    public function persist($entity) {
        $this->_em->persist($entity);
    }

    /**
     * @param mixed $entity
     */
    public function merge($entity) {
        $this->_em->merge($entity);
    }

    /**
     * @param iterable $entityList
     */
    public function mergeMultiple(iterable $entities) {
        foreach($entities as $entity) {
            $this->merge($entity);
        }
    }

    public function flush() {
        $this->_em->flush();
    }

    /**
     * @param iterable $entities
     * @param bool $flush
     */
    public function deleteMultiple(iterable $entities, bool $flush = true) {
        foreach($entities as $entity) {
            $this->_em->remove($entity);
        }
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param mixed $entity
     * @param bool $flush
     */
    public function delete($entity, bool $flush = true){
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function deleteByIds(array $ids) {
        $qb = $this->createQueryBuilder('br');
        $qb->delete();
        $qb->where($qb->expr()->in('br.id', ':ids'));
        $qb->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     *
     * @param array $ids
     * @return mixed
     */
    public function getByIds(array $ids) {
        $qb = $this->createQueryBuilder('br');
        $qb->where($qb->expr()->in('br.id', ':ids'));
        $qb->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }
}