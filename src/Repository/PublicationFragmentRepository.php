<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\PublicationFragment;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<PublicationFragment>
 */
class PublicationFragmentRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PublicationFragment::class, 'publicationFragment');
    }
}
