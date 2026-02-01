<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Event;

use Sowapps\SoCore\Service\SecurityService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * TODO Imported sources, require more test
 */
readonly class AccessDeniedSubscriber implements EventSubscriberInterface {

    public function __construct(
        protected UrlGeneratorInterface $router,
        protected SecurityService       $securityService,
        #[Autowire('%so_core.routing%')]
        protected array $configRouting,
    ) {
	}

	public function onKernelException(ExceptionEvent $event): void {
		$exception = $event->getThrowable();
        $stopPropagation = true;
        if( $exception instanceof AccessDeniedException ) {
            if( !$this->securityService->isAuthenticated() ) {
                // Not authenticated while checking if the current user is granted the role
                $this->onAuthenticationException($event);
            } else {
                // Authenticated, but the current user is not granted the role
                $this->onAccessDeniedException($event);
            }
        } else if( $exception instanceof AuthenticationException ) {
            // For now, never happens but it could be
            $this->onAuthenticationException($event);
        } else {
            $stopPropagation = false;
        }

        if( $stopPropagation ) {
            // Stop propagation (prevents the next exception listeners from being called)
            $event->stopPropagation();
        }
    }

    protected function onAccessDeniedException(ExceptionEvent $event): void {
        // Api route
        if( $this->isApiRequest($event) ) {
            $response = new JsonResponse(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
            $event->setResponse($response);
            return;
        }

        // Web Route
        // Redirect to user's home if logged in
        $user = $this->securityService->getCurrentUser();
        if( $user ) {
            $route = $this->securityService->isAdmin($user) ? $this->configRouting['route']['admin_default'] : $this->configRouting['route']['public_default'];
            $response = new RedirectResponse($this->router->generate($route), 302);
            $event->setResponse($response);
        }
    }

    protected function onAuthenticationException(ExceptionEvent $event): void {
        // Api route
        if( $this->isApiRequest($event) ) {
            $response = new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            $event->setResponse($response);
            return;
        }

        // Web Route
        // Nothing
    }

    protected function isApiRequest(ExceptionEvent $event): bool {
        $request = $event->getRequest();

        return str_starts_with($request->getPathInfo(), '/api');
    }

	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::EXCEPTION => [
				// The priority must be greater than the Security HTTP ExceptionListener, to make sure it's called before the default exception listener
				['onKernelException', 2],
			],
		];
	}

}
