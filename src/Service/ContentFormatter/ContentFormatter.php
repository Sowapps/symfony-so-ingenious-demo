<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service\ContentFormatter;

use App\Core\ContentFormatterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Adaptive formatter depending on requested type
 */
class ContentFormatter implements ServiceSubscriberInterface {

    public const FORMAT_HTML = 'html';
    public const FORMAT_TEXT = 'text';
    public const FORMAT_MARKDOWN = 'markdown';

    private static array $formats = [
        self::FORMAT_HTML     => HtmlFormatter::class,
        self::FORMAT_TEXT     => TextFormatter::class,
        self::FORMAT_MARKDOWN => MarkdownFormatter::class,
    ];

    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    /**
     * Fragment properties are using an array{format: string, text: string}
     *
     * @param array $content
     * @return string
     */
    public function formatFromArray(array $content): string {
        return $this->format($content['text'], $content['format']);
    }

    /**
     * Format for a Fragment property : array{format: string, text: string}
     *
     * @param string $text
     * @param string $format
     * @return string
     */
    public function format(string $text, string $format): string {
        return $this->getFormatter($format)->format($text);
    }

    public function getFormatter(string $format): ContentFormatterInterface {
        /** @var ContentFormatterInterface $formatter */
        $formatter = $this->container->get($format);

        return $formatter;
        //        $formatter = $this->formats[$format] ?? null;
        //        if(!$formatter) {
        //            throw new ValueError("Invalid format \"$format\", this format is unknown.");
        //        }
        //
        //        return $this->container->get($formatter);
    }

    public static function getSubscribedServices(): array {
        return self::$formats;
    }

}
