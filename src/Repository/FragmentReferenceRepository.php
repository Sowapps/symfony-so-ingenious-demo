<?php

namespace App\Repository;

use App\Entity\FragmentReference;
use Doctrine\Persistence\ManagerRegistry;
use Sowapps\SoCore\Core\DBAL\AbstractRepository;

/**
 * @extends AbstractRepository<FragmentReference>
 */
class FragmentReferenceRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, FragmentReference::class, 'fragmentReference');
    }
}
