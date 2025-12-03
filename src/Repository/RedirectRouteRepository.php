<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\RedirectRoute;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<RedirectRoute>
 */
class RedirectRouteRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, RedirectRoute::class, 'redirectRoute');
    }
}
