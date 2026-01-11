<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Event;

use Sowapps\SoCore\Service\SecurityService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * TODO Imported sources, require more test
 */
readonly class AccessDeniedSubscriber implements EventSubscriberInterface {

    public function __construct(
        protected UrlGeneratorInterface $router,
        protected SecurityService       $securityService,
    ) {
	}

	public function onKernelException(ExceptionEvent $event): void {
		$exception = $event->getThrowable();
		if( !$exception instanceof AccessDeniedException ) {
			return;
		}

		// Redirect to user's home if logged in
        $user = $this->securityService->getCurrentUser();
		if( $user ) {
            $route = $this->securityService->isAdmin($user) ? 'admin_home' : 'user_home';
			$event->setResponse(new RedirectResponse($this->router->generate($route), 302));
		}

		// Or stop propagation (prevents the next exception listeners from being called)
		//$event->stopPropagation();
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
