<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    //TODO: implement API exchange rate feeding
    private $exchangeRatesUSD = [
        'JPYUSD' => 0.0091, //how many US dollars do I pay for one yen?
        'GBPUSD' => 1.42, // how many US dollars do I pay for one pound sterling?
        'EURUSD' => 1.22 // how many US dollars do I pay for one euro?
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }


    /**
     * @return number of products found for a given $categoryName
     */
    public function countByCategory(string $categoryName):int
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.category = :val')
            ->setParameter('val', $categoryName)
            ->getQuery()
            ->getResult();

        return count($query);
    }

    public function findAllByCategory(string $categoryName)
    {
        $result = $this->createQueryBuilder('p')
            ->andWhere('p.category = :val')
            ->setParameter('val', $categoryName)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param string $categoryName
     * @return float
     * @description gets all the products values, exchange them to USD and return total product value in USD for a given $categoryName
     */
    public function sumAllProductValuesPerCategoryInUSD(string $categoryName): float
    {
        $productsWithinTheCategory = $this->findAllByCategory($categoryName);

        $totalValueInUsd = 0;

        foreach ($productsWithinTheCategory as $product) {
            $price = $product->getPrice();

            switch ($price[0]) {
                case '£':
                    $totalValueInUsd += floatval(ltrim($price, '£')) * $this->exchangeRatesUSD['GBPUSD'];

                    break;
                case '$':
                    $totalValueInUsd += floatval(ltrim($price, '$'));

                    break;
                case '¥':
                    $totalValueInUsd += floatval(ltrim($price, '¥')) * $this->exchangeRatesUSD['JPYUSD'];

                    break;
                case '€':
                    $totalValueInUsd += floatval(ltrim($price, '€')) * $this->exchangeRatesUSD['EURUSD'];

                    break;
            }
        }
        return $totalValueInUsd;
    }

    /**
     * @param mixed $productId
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     */
    public function doesProductExist(mixed $productId): bool
    {
        try {
            $productId = (int)$productId;

            $result = $this->createQueryBuilder('p')
                ->andWhere('p.productId = :productId')
                ->setParameter('productId', $productId)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            return !($result === null);
        } catch (\Doctrine\ORM\NonUniqueResultException $exception) {
            throw $exception;
        }
    }
}
