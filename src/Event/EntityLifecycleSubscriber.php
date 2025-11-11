<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Event;

use App\Service\UserService;
use DateTimeImmutable;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Sowapps\SoCore\Entity\AbstractEntity;

/**
 * Follow the lifecycle of an AbstractEntity and fill it with context values
 * TODO Imported sources, require more test
 */
class EntityLifecycleSubscriber implements EventSubscriber {

	protected UserService $userService;

	/**
	 * EntityLifecycleSubscriber constructor
	 *
	 * @param UserService $userService
	 */
	public function __construct(UserService $userService) {
		$this->userService = $userService;
	}

	/**
	 * @inheritDoc
	 */
	public function getSubscribedEvents(): array {
		return [
			Events::prePersist,
		];
	}

	public function prePersist(LifecycleEventArgs $args): void {
		$entity = $args->getObject();
		if( !($entity instanceof AbstractEntity) ) {
			return;
		}

		if( !$entity->getCreateDate() ) {
			$entity->setCreateDate(new DateTimeImmutable());
		}
		if( !$entity->getCreateUser() ) {
			$currentUser = $this->userService->getCurrent();
			if( $currentUser ) {
				$entity->setCreateUser($currentUser);
			}
		}
		if( !$entity->getCreateIp() ) {
			$entity->setCreateIp($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
		}
	}
}
