<?php

namespace App\Repository;

use App\Entity\RedirectRoute;
use Doctrine\Persistence\ManagerRegistry;
use Sowapps\SoCore\Core\DBAL\AbstractRepository;

/**
 * @extends AbstractRepository<RedirectRoute>
 */
class RedirectRouteRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, RedirectRoute::class, 'redirectRoute');
    }
}
