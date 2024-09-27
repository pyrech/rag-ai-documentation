<?php

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Section>
 */
class SectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Section::class);
    }

    /**
     * @param float[] $embeddings
     *
     * @return Section[]
     */
    public function findNearest(array $embeddings): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.content IS NOT NULL AND s.content != \'\'')
            ->orderBy('cosine_similarity(s.embeddings, :embeddings)', 'DESC')
            ->setParameter('embeddings', $embeddings, 'vector')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }
}
