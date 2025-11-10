<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class SecurityService {

    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    const ROLE_IMPERSONATE = 'ROLE_IMPERSONATE';

    private ?user $currentUser = null;

    public function __construct(
        private readonly Security                       $security,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }
	
	public function getLoginUrl(string $target): string {
		return '/login?target=' . $this->encodeTargetUri($target);
	}
	
	public function encodeTargetUri(string $uri): string {
		return urlencode($uri);
	}

    /**
     * @return string[]
     */
    public function getAllRoles(): array {
        return [self::ROLE_USER, self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN, self::ROLE_IMPERSONATE];
    }

    /**
     * Resolve all granted roles for user
     *
     * @param User $user
     * @return string[]
     */
    public function getUserRoles(user $user): array {
        return array_filter($this->getAllRoles(), fn(string $role) => $this->isGranted($user, $role));
    }

    public function isAuthenticated(): bool {
        return !!$this->getCurrentUser();
    }

    public function isAdmin(?User $user): bool {
        return $this->isGranted($user, self::ROLE_ADMIN);
    }

    public function isGranted(?User $user, $attribute, $object = null): bool {
        if( !$user ) {
            return false;
        }
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        return ($this->accessDecisionManager->decide($token, [$attribute], $object));
    }

    public function getCurrentUser(): ?User {
        if( $this->currentUser ) {
            return $this->currentUser;
        }
        $user = $this->security->getUser();

        return $user instanceof User ? $user : null;
    }

    public function setCurrentUser(?User $currentUser): void {
        $this->currentUser = $currentUser;
    }

    public function getRemoteIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

}
