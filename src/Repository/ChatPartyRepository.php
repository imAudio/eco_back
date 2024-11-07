<?php

namespace App\Repository;

use App\Entity\ChatParty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChatParty>
 */
class ChatPartyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatParty::class);
    }

    //    /**
    //     * @return ChatParty[] Returns an array of ChatParty objects
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

    //    public function findOneBySomeField($value): ?ChatParty
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findMessagesByPartyId(int $partyId): array
{
    $entityManager = $this->getEntityManager();

    $query = $entityManager->createQuery(
        'SELECT c.id, c.content, u.id AS user_id, u.email, p.id AS party_id
        FROM App\Entity\ChatParty c
        JOIN c.user u
        JOIN c.party p
        WHERE p.id = :partyId'
    )->setParameter('partyId', $partyId);

    return $query->getResult();
}

}
