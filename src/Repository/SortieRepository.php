<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;


/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function filter($datas, Participant $user){

        $queryBuilder = $this->createQueryBuilder('s')
            ->join('s.Campus', 'c')
            ->addSelect('c')
            ->andWhere('c = :campus')
            ->andWhere('s.nom LIKE :nom')
            ->setParameter('campus', $datas['campus']->getData())
            ->setParameter('nom', '%'.$datas['nom']->getData().'%')
        ;

        if($datas['organisateur']->getData()) {
            $queryBuilder
                ->join('s.organisateur', 'o')
                ->addSelect('o')
                ->andWhere('o = :user1')
                ->setParameter('user1',$user)
                ;
        }
        if($datas['inscrit']->getData() && !$datas['non_inscrit']->getData()) {
            $queryBuilder
                ->join('s.participants', 'p')
                ->addSelect('p')
                ->andWhere('p = :user2')
                ->setParameter('user2',$user)
            ;
        }/*else if(!$datas['inscrit']->getData() && $datas['non_inscrit']->getData()) {
            $queryBuilder
                ->join('s.participants', 'p')
                ->addSelect('p')
                ->andWhere('p NOT :user3')
                ->setParameter('user3',$user)
            ;
        }*/

        if($datas['dateHeureDebut']->getData() != null){
            $queryBuilder
                ->setParameter('start', $datas['dateHeureDebut']->getData())
                ->andWhere('s.dateHeureDebut >= :start');
        }
        if($datas['dateHeureFin']->getData() != null){
            $queryBuilder
                ->setParameter('end', $datas['dateHeureFin']->getData())
                ->andWhere('s.dateHeureDebut <= :end');
        }

        if($datas['passees']->getData()){
            $queryBuilder
                ->setParameter('now', new \DateTime())
                ->andWhere('s.dateHeureDebut < :now');
        }else{
            $queryBuilder
                ->setParameter('now', new \DateTime())
                ->andWhere('s.dateHeureDebut >= :now');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
