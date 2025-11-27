<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\FragmentRoute;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<FragmentRoute>
 */
class FragmentRouteRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, FragmentRoute::class, 'fragmentRoute');
    }
}
