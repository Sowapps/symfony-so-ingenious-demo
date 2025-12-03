<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use RuntimeException;
use Sowapps\SoCore\Entity\Language;
use Sowapps\SoCore\Repository\LanguageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service to manager languages
 */
class LanguageService {
    protected ?Language $activeLanguage = null;

    /**
     * LanguageService constructor
     *
     * @param RequestStack $requestStack
     * @param LanguageRepository $languageRepository
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LanguageRepository $languageRepository
    ) {
    }

    public function getActiveLanguage(): Language {
        if( !$this->activeLanguage ) {
            $request = $this->requestStack->getCurrentRequest();
            $locale = $request?->getLocale();

            $this->activeLanguage = $this->getLanguageByLocale($locale) ?? $this->getDefaultLocaleLanguage();
        }
        return $this->activeLanguage;
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
     * @deprecated Use LanguageRepository::findAll ? (require confirmation)
     */
    public function getLanguages(): array {
        return $this->languageRepository->findAll();
    }

    public function getLanguageByLocale(?string $locale): ?Language {
        if( !$locale ) {
            return null;
        }
        return $this->languageRepository->findByLocale($locale);
    }

    public function getDefaultLocaleLanguage(): Language {
        $language = $this->getLanguageByLocale($this->getDefaultLocale());
        if( !$language ) {
            throw new RuntimeException('The default locale has no registered language');
        }
        return $language;
    }

    public function getDefaultLocale(): string {
        return 'fr';
    }

}
