<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\Route;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Route>
 */
class RouteRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Route::class, 'route');
    }
}
