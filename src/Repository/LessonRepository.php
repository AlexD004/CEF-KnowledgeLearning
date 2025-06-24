<?php

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lesson>
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    //    /**
    //     * @return Lesson[] Returns an array of Lesson objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Lesson
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    public function findByFilters(?string $themeId, ?string $cursusId, ?string $maxPrice): array
    {
        $qb = $this->createQueryBuilder('l')
            ->join('l.cursus', 'c')
            ->join('c.theme', 't')
            ->addSelect('c', 't');

        if ($themeId) {
            $qb->andWhere('t.id = :themeId')->setParameter('themeId', $themeId);
        }

        if ($cursusId) {
            $qb->andWhere('c.id = :cursusId')->setParameter('cursusId', $cursusId);
        }

        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $qb->andWhere('l.price <= :maxPrice')->setParameter('maxPrice', $maxPrice);
        }

        return $qb->getQuery()->getResult();
    }

}

