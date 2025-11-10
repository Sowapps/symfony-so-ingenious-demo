<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use App\Entity\ClientSession;
use App\Entity\User;
use App\Repository\ClientSessionRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\Session;

class ClientSessionService {
	
	const KEY_AUTHENTICATED_USER_ID = 'AUTH_USER_ID';
	const KEY_CLIENT_SESSION_ID = 'CLIENT_SESSION_ID';

    public function __construct(
        protected EntityService           $entityService,
        protected ClientSessionRepository $clientSessionRepository,
        protected UserRepository          $userRepository,
    ) {
    }

    public function disconnectClientSession(Session $session): bool {
        return (bool)$session->remove(ClientSessionService::KEY_CLIENT_SESSION_ID);
    }

    private function getSavedClientSession(Session $session): ?ClientSession {
        $clientSessionId = $session->get(static::KEY_CLIENT_SESSION_ID);

        return $clientSessionId ? $this->clientSessionRepository->find($clientSessionId) : null;
    }

    public function assignNewClientSession(Session $session, ?User $user): ClientSession {
        $clientSession = new ClientSession();
        if( $user ) {
            $clientSession->setUser($user);
        }
        $this->entityService->create($clientSession)->flush();
        $session->set(static::KEY_CLIENT_SESSION_ID, $clientSession->getId());

        return $clientSession;
    }

    public function getClientSession(Session $session, ?User $user): ClientSession {
        $clientSession = $this->getSavedClientSession($session);
        if( !$clientSession ) {
            $clientSession = $this->assignNewClientSession($session, $user);
        }

        return $clientSession;
    }

}
