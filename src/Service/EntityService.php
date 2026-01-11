<?php

namespace App\Service;

use App\Core\Entity\PaginatedResult;
use App\Core\ProcessOption\PaginationOptions;
use App\Exception\ConstraintValidationException;
use ArrayIterator;
use AutoMapper\AutoMapperInterface;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RuntimeException;
use Sowapps\SoCore\Core\Entity\AbstractEntity;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Traversable;

class EntityService {

	/**
	 * Prevent any change in database using this service
	 *
	 * @var bool
	 */
	private bool $dryRun = false;

    public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly ValidatorInterface     $validator,
		private readonly AutoMapperInterface    $autoMapper
    ) {
    }

    public function mapDto(object $dto, AbstractEntity $entity): void {
        $this->autoMapper->map($dto, $entity);
    }

    public function countChanges(AbstractEntity $entity): int {
        $uow = $this->entityManager->getUnitOfWork();
        $uow->computeChangeSets();

        return count($uow->getEntityChangeSet($entity));
    }

    /**
	 * @throws ConstraintValidationException
     */
	public function validate(AbstractEntity $entity, ?array $groups = null): void {
        $errors = $this->validator->validate($entity, null, $groups);
        if( $errors->count() ) {
			throw new ConstraintValidationException($errors);
        }
    }

    public function paginateQuery(QueryBuilder $query, PaginationOptions $pagination): PaginatedResult {
        $resultPerPage = $pagination->getPageLimit();
        $page = $resultPerPage ? $pagination->getPage() : 1;// if selecting all, force first page
        $resultMax = $page * $resultPerPage;
        $resultMin = $resultMax - $resultPerPage;
        $count = $this->countQueryRows($query);
        if( ($count - 1) >= $resultMin ) {
            // There are results for this page
            if( $resultPerPage ) {
                $query
                    ->setMaxResults($resultPerPage)
                    ->setFirstResult($resultMin);
            }
            $results = $query
                ->getQuery()
                ->toIterable();
        } else {
            $results = new ArrayIterator();
        }

        return $this->getPaginatedResult($results, $page, $resultPerPage, $count);
    }

    public function countQueryRows(QueryBuilder $query): int {
        $paginator = new Paginator($query);

        return count($paginator);
    }

    protected function getPaginatedResult(Traversable $results, int $page, int $resultPerPage, int $count): PaginatedResult {
        return new PaginatedResult($results, $page, $resultPerPage, $count);
    }

    public function paginateCollection(Collection $collection, array $pagination): PaginatedResult {
        if( !($collection instanceof Selectable) ) {
            throw new RuntimeException('Non-selectable collection, can not paginate results');
        }
        [$page, $resultPerPage] = $pagination;
        $page++;// 0-indexed to 1-indexed
        $resultMax = $page * $resultPerPage;
        $resultMin = $resultMax - $resultPerPage;

        // Require fetch="EXTRA_LAZY" on relations to prevent loading
        $count = $collection->count();

        $criteria = Criteria::create();
        $criteria
            ->setMaxResults($resultPerPage)
            ->setFirstResult($resultMin);
        $results = $collection->matching($criteria);

        return $this->getPaginatedResult($results, $page, $resultPerPage, $count);
	}

	public function createList(array $entities): static {
		foreach( $entities as $entity ) {
			$this->create($entity);
			//			$this->setupCreate($entity);
			//			$this->entityManager->persist($entity);
		}

		return $this;
	}

    public function create(AbstractEntity $entity): static {
        $this->setupCreate($entity);
        $this->entityManager->persist($entity);

        return $this;
    }

    protected function setupCreate(AbstractEntity $entity): void {
        //		$entity->setId(Uuid::v4());// Uuid4 is totally random
        //		$entity->setCreationDate(new DateTime());
        //		$entity->setCreationUser($this->security->getUser());
        //		$entity->setCreationIp($this->requestStack->getCurrentRequest()?->getClientIp() ?? '127.0.0.1');
    }

    public function refresh(AbstractEntity &$entity): void {
        if( $entity->isNew() ) {
            // Manually refresh new entities !
            //			$entity->refresh($this);

            // Else no way to refresh it properly
            return;
        }
        // Entity is persisted in db, we would to reload it
        $reload = true;
        if( $this->entityManager->contains($entity) ) {
            // Persisted by doctrine
            $this->entityManager->refresh($entity);
            $reload = false;
        }
        if( $reload ) {
            // Not managed by Doctrine but (should be) existing, so we reload
            $this->reload($entity);
        }
    }

    /**
     * @param AbstractEntity|null $entity
     * @param $newIsNull
     * @return bool
     * @see getFreshEntity() may be better
     */
    public function reload(?AbstractEntity &$entity, $newIsNull = true): bool {
        if( !$entity ) {
            return false;
        }
        if( $entity->isNew() ) {
            if( $newIsNull ) {
                $entity = null;
            }
        } else {
            $refreshedEntity = $this->entityManager->getRepository(get_class($entity))->find($entity->getId());
            if( $refreshedEntity ) {
                $entity = $refreshedEntity;
            } else {
                // Not existing in db for real, may be stored in session
                // Clone to make it new
                $entity = clone $entity;
                // Manually refresh new entities !
                if( method_exists($entity, 'refresh') ) {
                    $entity->refresh($this);
                }
            }
        }

        return true;
    }

    /**
     * @template T of AbstractEntity
     * @param T $entity
     * @return T
     * @throws ORMException
     */
    public function getFreshEntity(AbstractEntity $entity): AbstractEntity {
        return $this->entityManager->getReference(get_class($entity), $entity->getId());
    }

    public function getRepository(string $class): EntityRepository {
        return $this->entityManager->getRepository($class);
    }

    public function updateList(array $entities): static {
        foreach( $entities as $entity ) {
            $this->update($entity);
        }

        return $this;
    }

    public function update(AbstractEntity $entity): static {
        $this->setupUpdate($entity);
        $this->entityManager->persist($entity);

        return $this;
    }

    protected function setupUpdate(AbstractEntity $entity): void {
        if( method_exists($entity, 'setModificationDate') ) {
            $entity->setModificationDate(new DateTime());
        }
        //		if( method_exists($entity, 'onUpdate') ) {
        //			$entity->onUpdate();
        //		}
    }

    public function remove(AbstractEntity $entity): static {
		if( $this->isDryRun() ) {
			// Do nothing
			return $this;
		}
        $this->entityManager->remove($entity);

        return $this;
    }

    public function detachList(array $entities): void {
        foreach( $entities as $entity ) {
            $this->entityManager->detach($entity);
        }
    }

    /**
     * @param AbstractEntity $entity
     * @param LockMode|int $lockMode
     * @param int|null $lockVersion
     * @return void
     * @throws OptimisticLockException
     */
    public function lock(AbstractEntity $entity, LockMode|int $lockMode = LockMode::PESSIMISTIC_READ, int|null $lockVersion = null): void {
        $this->entityManager->lock($entity, $lockMode, $lockVersion);
	}

	public function startTransaction(): void {
		if( $this->isDryRun() ) {
			// Do nothing
			return;
		}
		$this->entityManager->beginTransaction();
	}

	public function commitTransaction(): void {
		if( $this->isDryRun() ) {
			// Do nothing
			return;
		}
		$this->entityManager->commit();
	}

	public function rollbackTransaction(): void {
		if( $this->isDryRun() ) {
			// Do nothing
			return;
		}
		$this->entityManager->rollback();
	}

	public function wrapInTransaction(callable $func): mixed {
		if( $this->isDryRun() ) {
			// Call with no transaction, no change in database
			return call_user_func($func);
		}

		return $this->entityManager->wrapInTransaction($func);
	}

    public function flush(): void {
		if( $this->isDryRun() ) {
			// Do nothing
			return;
		}
        $this->entityManager->flush();
    }

    public function clear(): void {
        $this->entityManager->clear();
    }

    public function clearAllEntities(array $entityClasses): void {
        $connection = $this->entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();

        // Truncate ignores transaction and commit immediately
        // Disable foreign key checks
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        foreach( $entityClasses as $class ) {
            $cmd = $this->entityManager->getClassMetadata($class);
            $sql = $dbPlatform->getTruncateTableSql($cmd->getTableName());
            $connection->executeStatement($sql);
        }

        // Enable foreign key checks
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function getAttachedCount(): int {
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $identityMap = $unitOfWork->getIdentityMap();

        $count = 0;
        foreach( $identityMap as $entities ) {
            $count += count($entities);
        }

        return $count;
	}

	public function isDryRun(): bool {
		return $this->dryRun;
	}

	public function setDryRun(bool $dryRun): EntityService {
		$this->dryRun = $dryRun;

		return $this;
	}

}
