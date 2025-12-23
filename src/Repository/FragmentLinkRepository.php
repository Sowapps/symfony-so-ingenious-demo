<?php

namespace App\Repository;

use App\Entity\FragmentLink;
use Doctrine\Persistence\ManagerRegistry;
use Sowapps\SoCore\Core\DBAL\AbstractRepository;

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
