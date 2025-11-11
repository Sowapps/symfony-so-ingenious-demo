<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use App\Entity\User;
use Sowapps\SoCore\Entity\Language;
use Sowapps\SoCore\Repository\LanguageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Service to manager languages
 */
class LanguageService {
    protected ?string $currentLocale = null;

    /**
     * LanguageService constructor
     *
     * @param RouterInterface $router
     * @param LanguageRepository $languageRepository
     */
    public function __construct(
        private readonly RouterInterface $router,
        private readonly LanguageRepository $languageRepository
    ) {
    }

    /**
     * Use user language's locale to generate links in app.
     * It's very useful for email sending.
     *
     * @param User $user
     */
    public function useUserLocale(User $user): void {
        if( !$this->currentLocale ) {
            $this->currentLocale = $this->router->getContext()->getParameter('_locale');
        }
        $language = $user->getLanguage();
        $this->router->getContext()->setParameter('_locale', $language->getLocale());
    }

    /**
     * Restore request local into the router
     *
     * @return bool
     */
    public function restoreLocale(): bool {
        if( $this->currentLocale ) {
            $this->router->getContext()->setParameter('_locale', $this->currentLocale);

            return true;
        }

        return false;
    }

    public function getBestUserLanguage(Request $request): ?Language {
        foreach( $this->getClientPreferredLocales($request) as $locale ) {
            $language = $this->getLanguageByLocale($locale);
            if( $language ) {
                // Return first matching language
                return $language;
            }
            if( strlen($locale) < 5 ) {
                // Try to find a language with this local as primary code
                // e.g for fr_CA,fr App will look for fr_FR
                $language = $this->getLanguageByLocale($locale);
                if( $language ) {
                    // Return first matching language
                    return $language;
                }
            }
        }

        return $this->getDefaultLocaleLanguage();
    }

    public function getClientPreferredLocales(Request $request): array {
        //en-US,en;q=0.9,fr-FR;q=0.8,fr;q=0.7
        $acceptLanguageString = $request->headers->get('accept-language');
        if( !$acceptLanguageString ) {
            return [];
        }
        $httpLocales = explode(',', $acceptLanguageString);
        $locales = [];
        foreach( $httpLocales as $httpLocale ) {
            $locales[] = $this->getLocaleFromHttpFormat($httpLocale);
        }

        return $locales;
    }

    public function getLocaleFromHttpFormat($httpLocale): string {
        if( strlen($httpLocale) > 7 ) {
            [$httpLocale,] = explode(';', $httpLocale);
        }
        if( strlen($httpLocale) > 3 ) {
            $httpLocale = strtr($httpLocale, '-', '_');
        }

        return $httpLocale;
    }

    /**
     * @param string $locale
     * @return Language|null
     * @deprecated Use LanguageRepository::findByPrimary ? (require confirmation)
     */
    public function getLanguageByPrimary(string $locale): ?Language {
        return $this->languageRepository->findByPrimary($locale);
    }

    /**
     * @return Language[]
     * @deprecated Use LanguageRepository::findAll ? (require confirmation)
     */
    public function getLanguages(): array {
        return $this->languageRepository->findAll();
    }

    /**
     * @param string $locale
     * @return Language|null
     */
    public function getLanguageByLocale(string $locale): ?Language {
        return $this->languageRepository->findByLocale($locale);
    }

    public function getDefaultLocaleLanguage(): ?Language {
        return $this->getLanguageByLocale($this->getDefaultLocale());
    }

    public function getDefaultLocale(): string {
        return 'fr';
    }

}
