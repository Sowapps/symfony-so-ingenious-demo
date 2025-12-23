<?php

namespace App\Repository;

use App\Entity\SlotFragment;
use Doctrine\Persistence\ManagerRegistry;
use Sowapps\SoCore\Core\DBAL\AbstractRepository;

/**
 * @extends AbstractRepository<SlotFragment>
 */
class SlotFragmentRepository extends AbstractRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, SlotFragment::class, 'slotFragment');
    }

    public function getByName(string $name): ?SlotFragment {
        return $this->findOneBy(['slot' => $name]);
    }
}
