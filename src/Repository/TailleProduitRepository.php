<?php

namespace App\Repository;

use App\Entity\TailleProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TailleProduit>
 *
 * @method TailleProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method TailleProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method TailleProduit[]    findAll()
 * @method TailleProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TailleProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TailleProduit::class);
    }

//    /**
//     * @return TailleProduit[] Returns an array of TailleProduit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TailleProduit
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
