<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\Article;
use App\Sowapps\SoIngenious\PublicationStatus;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Article>
 */
class ArticleRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Article::class, 'article');
    }

    /**
     * @return list<Article>
     */
    public function listEnabled(): array {
        return $this->queryPublished()->getQuery()->getResult();
    }

    public function queryPublished(): QueryBuilder {
        return $this->query()
            ->andWhere('article.status = :statusPublished')
            ->setParameter('statusPublished', PublicationStatus::Published);
    }
}
