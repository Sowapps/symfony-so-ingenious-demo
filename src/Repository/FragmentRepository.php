<?php

namespace App\Repository;

use App\Core\Entity\AbstractRepository;
use App\Entity\Fragment;
use App\Entity\LocalizedUnit;
use Doctrine\Persistence\ManagerRegistry;
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

    public function getByLocalizedUnitAndLanguage(LocalizedUnit $unit, Language $language): ?Fragment {
        return $this->query()
            ->andWhere('fragment.localizedUnit = :unit')
            ->andWhere('fragment.language = :language')
            ->setParameter('unit', $unit)
            ->setParameter('language', $language)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * These filters would return a list of fragments
     */
    public function getSingleFilters(): array {
        return [
            'id'     => 'fragment.id',
            'slug'   => 'fragment.slug',
        ];
    }

    /**
     * These filters would return a list of fragments
     *  Must return a field with the same name as the key
     */
    public function getListFilters(): array {
        return [
            'status' => 'fragment.status',
            //          'date' => 'fragment.publishDate',// TODO Implement with multiple operators ? Too complexe for now
            'year'   => 'YEAR(f.publishDate) AS year',
            'month'  => 'MONTH(f.publishDate) AS month',
        ];
    }
}
