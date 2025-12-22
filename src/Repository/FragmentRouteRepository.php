<?php

namespace App\Repository;

use App\Entity\FragmentRoute;
use Doctrine\Persistence\ManagerRegistry;
use Sowapps\SoCore\Core\DBAL\AbstractRepository;

/**
 * @extends AbstractRepository<FragmentRoute>
 */
class FragmentRouteRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, FragmentRoute::class, 'fragmentRoute');
    }
}
