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

    /**
     * These filters would return a list of fragments
     */
    public function getSingleFilters(): array {
        return [
            'id'   => 'publicationFragment.id',
            'slug' => 'publicationFragment.slug',
        ];
    }

    /**
     * These filters would return a list of fragments
     * Must return a field with the same name as the key
     * Our system will add alias to the selector, and to fetch items, it will be hidden
     */
    public function getListFilters(): array {
        return [
            'status' => 'publicationFragment.status',
            //          'date' => 'publicationFragment.publishDate',// TODO Implement with multiple operators ? Too complexe for now
            'year'   => 'YEAR(publicationFragment.publishDate)',
            'month'  => 'MONTH(publicationFragment.publishDate)',
        ];
    }
}
