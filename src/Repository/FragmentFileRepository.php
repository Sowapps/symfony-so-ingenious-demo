<?php

namespace App\Repository;

use App\Entity\FragmentFile;
use Doctrine\Persistence\ManagerRegistry;
use Sowapps\SoCore\Core\DBAL\AbstractRepository;

/**
 * @extends AbstractRepository<FragmentFile>
 */
class FragmentFileRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, FragmentFile::class, 'fragmentFile');
    }
}
