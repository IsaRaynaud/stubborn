<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Retourne la liste des produits « mis en avant ».
     *
     * @return Product[]
     */
    public function findFeatured(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isFeatured = :featured')
            ->setParameter('featured', true)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //Trie les produits par prix
    public function findByPriceRange(int $minEuro, int $maxEuro): array
    {
        $minCents = $minEuro * 100;
        $maxCents = ($maxEuro + 1) * 100 - 1;

        return $this->createQueryBuilder('p')
            ->andwhere('p.price BETWEEN :min AND :max')
            ->setParameter('min', $minCents)
            ->setParameter('max', $maxCents)
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
