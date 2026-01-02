<?php

namespace App\Repository;

use App\Entity\Fragment;
use App\Entity\LocalizedUnit;
use Doctrine\Persistence\ManagerRegistry;
use Sowapps\SoCore\Core\DBAL\AbstractRepository;
use Sowapps\SoCore\Entity\Language;

/**
 * @extends AbstractRepository<Fragment>
 */
class FragmentRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fragment::class, 'fragment');
    }

    public function getByReference(string $reference): ?Fragment {
        return $this->query()
            ->join('fragment.parentReferences', 'parentReference')
            ->andWhere('parentReference.name = :name')
            ->setParameter('name', $reference)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getByLocalizedUnitAndLanguage(LocalizedUnit $unit, Language $language): ?Fragment {
        return $this->query()
            ->andWhere('fragment.localizedUnit = :unit')
            ->andWhere('fragment.language = :language')
            ->setParameter('unit', $unit)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
