<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Sowapps\SoCore\Service\AbstractUserService;
use Sowapps\SoCore\Service\StringService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Service to manage users
 */
class UserService extends AbstractUserService {

    public function __construct(
        UserPasswordHasherInterface    $passwordEncoder,
        AccessDecisionManagerInterface $accessDecisionManager,
        Security                       $security,
        StringService $stringService,
        #[Autowire(param: 'so_core.user')]
        array                          $config
    ) {
        parent::__construct($passwordEncoder, $accessDecisionManager, $security, $stringService, $config);
    }

    function getUserRepository(): UserRepository {
		return $this->entityManager->getRepository(User::class);
	}

}
