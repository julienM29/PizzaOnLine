<?php

namespace App\Repository;

use App\Entity\CategorieIngredient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategorieIngredient>
 *
 * @method CategorieIngredient|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorieIngredient|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorieIngredient[]    findAll()
 * @method CategorieIngredient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieIngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategorieIngredient::class);
    }

//    /**
//     * @return CategorieIngredient[] Returns an array of CategorieIngredient objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CategorieIngredient
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
