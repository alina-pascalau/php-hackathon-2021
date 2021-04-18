<?php

namespace App\Repository;

use App\Entity\Programme;
use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Programme|null find($id, $lockMode = null, $lockVersion = null)
 * @method Programme|null findOneBy(array $criteria, array $orderBy = null)
 * @method Programme[]    findAll()
 * @method Programme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgrammeRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Programme::class);
    }

    /**
     * Checks if the room is available
     * 
     * @param Room $room
     * @param \DateTime $starttime
     * @return int
     */
    public function checkIfAvailable(Room $room, \DateTime $starttime) : int
    {
    //TODO check for time interval
        return $this->createQueryBuilder('p')
                        ->select('count(p.id)')
                        ->andWhere('p.starttime = :starttime')
                        ->setParameter('starttime', $starttime)
                        ->andWhere('p.Room = :room')
                        ->setParameter('room', $room->getId())
                        ->getQuery()
                        ->getSingleScalarResult();
    }
    
}
