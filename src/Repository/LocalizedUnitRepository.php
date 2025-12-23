<?php

namespace App\Repository;

use App\Entity\LocalizedUnit;
use Doctrine\Persistence\ManagerRegistry;
use Sowapps\SoCore\Core\DBAL\AbstractRepository;

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
