<?php

namespace App\Repository;

use App\Entity\Entreprise;
use App\Entity\Job;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Job>
 *
 * @method Job|null find($id, $lockMode = null, $lockVersion = null)
 * @method Job|null findOneBy(array $criteria, array $orderBy = null)
 * @method Job[]    findAll()
 * @method Job[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

       /**
        * @return Job[]
        */
       public function findCurrentJobOf(User $user): array
       {
           return $this->createQueryBuilder('j')
               ->andWhere("j.employee = :user")
               ->andWhere("j.endingDate IS NULL")
               ->setParameter("user", $user)
               ->getQuery()
               ->getResult()
           ;
       }

       /**
        * @return Job[]
        */
        public function findJobsFromTo(User $user, $from, $to): array
        {
            return $this->createQueryBuilder('j')
                ->andWhere("j.employee = :user")
                ->andWhere("j.startingDate BETWEEN :from AND :to")
                ->andWhere("j.endingDate BETWEEN :from AND :to")
                ->setParameter("user", $user)
                ->setParameter("from", $from)
                ->setParameter("to", $to)
                ->getQuery()
                ->getResult()
            ;
        }

       /**
        * @return Job[]
        */
       public function findEmployeesOf(Entreprise $entreprise): array
       {
           return $this->createQueryBuilder('j')
               ->andWhere("j.entreprise = :entreprise")
               ->setParameter("entreprise", $entreprise)
               ->getQuery()
               ->getResult()
           ;
       }
}
