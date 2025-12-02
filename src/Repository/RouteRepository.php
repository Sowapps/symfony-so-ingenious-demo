<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\Route;
use Doctrine\Persistence\ManagerRegistry;
use Sowapps\SoCore\Entity\Language;

/**
 * @extends AbstractRepository<Route>
 */
class RouteRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Route::class, 'route');
    }

    public function getByName(string $name, Language $language): ?Route {
        return $this->query()
            ->join('route.localizedUnit', 'localizedUnit')
            ->andWhere('localizedUnit.name = :name')
            ->andWhere('route.language = :language')
            ->setParameter('name', $name)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
