<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\LocalizedUnit;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<LocalizedUnit>
 */
class LocalizedUnitRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalizedUnit::class, 'localizedUnit');
    }
}
