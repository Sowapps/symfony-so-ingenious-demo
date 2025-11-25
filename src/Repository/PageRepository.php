<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\Page;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Page>
 */
class PageRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class, 'page');
    }
}
