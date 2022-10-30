<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Sowapps\SoCore\Service\AbstractUserService;

class UserService extends AbstractUserService {
	
	function getUserRepository(): UserRepository {
		return $this->entityManager->getRepository(User::class);
	}
	
}
