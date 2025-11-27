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
