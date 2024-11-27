<?php

namespace App\Repository;

use App\Entity\Vendor;
use App\Enumerations\TableCategoryEnumeration;
use App\Enumerations\VendorAreaEnumeration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vendor>
 *
 * @method Vendor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vendor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vendor[]    findAll()
 * @method Vendor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VendorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vendor::class);
    }

    public function add(Vendor $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Vendor $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Vendor[] Returns an array of Vendor objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vendor
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findByFilter($filter): array
    {
        $query = $this->createQueryBuilder('v');
        if (!empty($filter['search'])) {
            $query
                ->andWhere('UPPER(v.name) LIKE :name')
                ->setParameter('name', "%{$filter['search']}%");
        }
        if (!empty($filter['status'])) {
            $query
                ->andWhere('v.status = :stat')
                ->setParameter('stat', $filter['status']);
        }
        if (!empty($filter['table']) && $filter['table'] != TableCategoryEnumeration::CATEGORY_MATURE) {
            $query
                ->andWhere('v.tableCategory = :table')
                ->setParameter('table', $filter['table']);
        } elseif (!empty($filter['table'])){
            $query
                ->andWhere('v.area = :area')
                ->setParameter('area', VendorAreaEnumeration::AREA_MATURE);

        }
        $output = $query->orderBy('v.id', 'ASC')
            ->getQuery()
            ->getResult();
        return $output;
    }
}
