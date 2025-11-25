<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\FragmentLink;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<FragmentLink>
 */
class FragmentLinkRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FragmentLink::class, 'fragmentLink');
    }
}
