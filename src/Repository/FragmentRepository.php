<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\Fragment;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Fragment>
 */
class FragmentRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fragment::class, 'fragment');
    }
}
