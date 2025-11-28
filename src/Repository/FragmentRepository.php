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

    public function getFilterCriteria(): array {
        return [ // TODO distinguish single item filters and list item filters
            'id'     => 'fragment.id',
            'slug'   => 'fragment.slug',
            'status' => 'fragment.status',
            //          'date' => 'fragment.publishDate',// TODO Implement with multiple operators ? Too complexe for now
            'year'   => 'YEAR(f.publishDate) AS year',
            'month'  => 'MONTH(f.publishDate) AS month',
        ];
    }
}
