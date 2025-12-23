<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Event;

use App\Service\LanguageService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listen requests to redirect requests with missing locale
 */
class LocaleSubscriber implements EventSubscriberInterface {
    private array $excludedPathPrefixes = [
        '/.well-known', '/_profiler', '/_wdt', '/assets', '/build', '/favicon.ico', '/robots.txt', '/api',
        // Sowapps bundles
        '/_socore',
    ];

    public function __construct(
        private readonly LanguageService $languageService
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void {
        // Uniquement requête principale
        if( !$event->isMainRequest() ) {
            return;
        }

        $request = $event->getRequest();

        $path = $request->getPathInfo();
        if( $this->isExcluded($path) ) {
            // Exclude non app frontend routes
            return;
        }

        // here we have a frontend route
        $locale = $this->extractPathLocale($path);// Could be something else than a locale
        $language = $locale ? $this->languageService->getLanguageByLocale($locale) : null;
        if( !$language ) {
            // No locale provided or invalid
            // So, we calculate the user language from request
            $redirectLanguage = $this->languageService->getBestUserLanguage($request);

            $qs = $request->getQueryString();
            $url = '/' . $redirectLanguage->getLocale() . $path . ($qs ? '?' . $qs : '');

            // Redirect to user preferred language
            $event->setResponse(new RedirectResponse($url));
            return;
        }

        // Symfony locale code and language locale code should always be the same.
        // So everything is done
    }

    private function isExcluded(string $pathInfo): bool {
        foreach( $this->excludedPathPrefixes as $prefix ) {
            if( str_starts_with($pathInfo, $prefix) ) {
                return true;
            }
        }
        return false;
    }

    public static function getSubscribedEvents(): array {
        return [
            // Higher priority than Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 64]],
        ];
    }

    private function extractPathLocale(string $path): ?string {
        $parts = explode('/', $path);
        return $parts[1] ?? null; // Could return a non-locale string
    }
}
