<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Event;

use App\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * TODO Imported sources, require more test
 */
class AccessDeniedSubscriber implements EventSubscriberInterface {

	private UrlGeneratorInterface $router;

	private UserService $userService;

	public function __construct(UrlGeneratorInterface $router, UserService $userService) {
		$this->router = $router;
		$this->userService = $userService;
	}

	public function onKernelException(ExceptionEvent $event): void {
		$exception = $event->getThrowable();
		if( !$exception instanceof AccessDeniedException ) {
			return;
		}

		// Redirect to user's home if logged in
		$user = $this->userService->getCurrent();
		if( $user ) {
			$route = $this->userService->isAdmin($user) ? 'admin_home' : 'user_home';
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
