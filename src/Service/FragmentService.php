<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use App\Entity\Fragment;
use App\Sowapps\SoIngenious\Template;
use DirectoryIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twig\Environment as Twig;
use ValueError;

/**
 * Service to manage fragments
 */
class FragmentService {

    public const PROPERTY_TYPE_STRING = 'string';
    public const PROPERTY_TYPE_RICH_TEXT = 'rich_text';
    public const PROPERTY_TYPE_OBJECT = 'object';// Require "properties" in signature
    public const PROPERTY_TYPE_LIST = 'list';// Require "items" in signature

    const TEMPLATE_SUFFIX = '.html.twig';

    private readonly SplFileInfo $templateFolder;

    public function __construct(
        #[Autowire(service: 'app.fragment')]
        private readonly TagAwareCacheInterface $cache,
        private readonly Twig                   $twig,
        private readonly EntityService          $entityService,
        #[Autowire(param: 'so_ingenious.template.path')]
        string                         $templatePath,
        #[Autowire(param: 'twig.default_path')]
        private readonly string        $twigTemplatePath,
    ) {
        $this->templateFolder = new SplFileInfo($templatePath);
    }

    public function getFragmentRendering(Fragment $fragment): string {
        return $this->renderFragment($fragment);// Disable cache

        //        $key = $this->getFragmentCacheKey($fragment);
        //        return $this->cache->get($key, function (ItemInterface $item) use ($fragment) {
        //            $item->tag(['fragment', 'fragment_' . $fragment->getId(), 'language_' . $fragment->getLanguage()->getId()]);
        //            $item->expiresAfter(86400);
        //            return $this->renderFragment($fragment);
        //        });

        //        if( !$fragment->getHtml() ) {
        //            $fragment->setHtml($this->renderFragment($fragment));
        //            $this->entityService->update($fragment);
        //            $this->entityService->flush();
        //        }
        //
        //        return $fragment->getHtml();
    }

    protected function getFragmentCacheKey(Fragment $fragment): string {
        return sprintf('fragment:%d', $fragment->getId());
    }

    public function clearCache(Fragment $fragment): void {
        $key = $this->getFragmentCacheKey($fragment);
        $this->cache->delete($key);
    }

    public function renderFragment(Fragment $fragment): string {
        $template = $this->getTemplate($fragment->getTemplateName());
        $values = [
            'template' => $template,
            'fragment' => $fragment,
        ];
        // Add dynamic values from properties._related
        $related = $fragment->getProperties()['_related'] ?? [];
        foreach( $related as $name => $reference ) {
            if( isset($values[$name]) ) {
                throw new RuntimeException(sprintf('Value "%s" is already defined.', $name));
            }
            $values[$name] = $this->entityService->getRepository($reference['class'])->find($reference['id']);
        }

        // Convert absolute path to relative path to Twig templates folder
        $path = substr($template->getPath(), strlen($this->twigTemplatePath));
        return $this->twig->render($path, $values);
    }

    public function getTemplate(string $name): Template {
        $fileInfo = new SplFileInfo($this->templateFolder->getRealPath() . '/' . $name . self::TEMPLATE_SUFFIX);
        if( !$fileInfo->isFile() ) {
            throw new RuntimeException(sprintf('Template "%s" does not exist.', $name));
        }
        $templateMeta = $this->extractTemplateMeta($fileInfo);
        return new Template(
            $fileInfo,
            $name,
            $templateMeta['label'],
            $templateMeta['description'],
            $templateMeta['kind'],
            $templateMeta['version'],
            $templateMeta['properties'],
            $templateMeta['children']
        );
    }

    /**
     * @return Template[]
     */
    public function listTemplates(): array {
        if( !$this->templateFolder->isDir() ) {
            throw new RuntimeException('Template folder does not exist or it is not a directory');
        }

        return $this->scanTemplates($this->templateFolder);
    }

    /**
     * @param SplFileInfo $folderInfo
     * @param string $prefix
     * @return Template[]
     */
    private function scanTemplates(SplFileInfo $folderInfo, string $prefix = ''): array {
        $list = [];
        foreach( new DirectoryIterator($folderInfo->getRealPath()) as $fileInfo ) {
            if( $fileInfo->isDir() && !$fileInfo->isDot() ) {
                $list = [
                    ...$list,
                    ...$this->scanTemplates($fileInfo, ($prefix ? $prefix . '/' : '') . $fileInfo->getFilename())
                ];
                continue;
            }
            if( !$fileInfo->isFile() || !str_ends_with($fileInfo->getFilename(), self::TEMPLATE_SUFFIX) ) {
                continue;
            }

            $name = ($prefix ? $prefix . '/' : '') . $fileInfo->getBasename(self::TEMPLATE_SUFFIX);
            $list[$name] = $this->getTemplate($name);
        }

        return $list;
    }

    public function extractTemplateMeta(SplFileInfo $fileInfo): array {
        $path = $fileInfo->getRealPath();
        $src = file_get_contents($path);

        // Capture {#--- ... ---#}
        if( !preg_match('/\{#- ?--(.*?)-- ?-#\}/s', $src, $m) ) {
            throw new RuntimeException(sprintf('Template meta data not found in %s', $path));
        }
        $yaml = trim($m[1]);

        $meta = Yaml::parse($yaml);

        // Validate
        if( !isset($meta['label']) ) {
            throw new RuntimeException(sprintf('Missing required label in template metadata of %s', $path));
        }

        // Normalize root values
        $meta['kind'] ??= 'fragment';
        $meta['description'] ??= null;
        $meta['version'] ??= 1;
        $meta['children'] ??= [];

        $this->normalizeRecursiveProperties($meta, '');

        // Normalize children
        // All children are required
        foreach( $meta['children'] as &$childSignature ) {
            $isMultiple = false;
            if( is_string($childSignature) ) {
                $isMultiple = $childSignature[0] === '*';
                $childSignature = ['template' => ltrim($childSignature, '*')];
            }
            $childSignature['multiple'] = $isMultiple;
            $childSignature['required'] = true;
        }

        return $meta;
    }

    protected function normalizeRecursiveProperties(array &$signature, string $name): void {
        $signature['properties'] ??= [];
        foreach( $signature['properties'] as $propertyName => &$propertySignature ) {
            $fullName = $name . '.' . $propertyName;
            $required = true;
            // If signature is a string, convert it into array
            if( is_string($propertySignature) ) {
                $required = !($propertySignature[0] === '?');
                $propertySignature = ['type' => ltrim($propertySignature, '?')];
            }
            // Now signature is an array

            $propertySignature['required'] = $required;
            switch( $propertySignature['type'] ) {
                case self::PROPERTY_TYPE_OBJECT:
                    if( !isset($propertySignature['properties']) ) {
                        throw new ValueError('Missing required properties of type object in template metadata of property "%s"', $fullName);
                    }
                    if( !is_array($propertySignature['properties']) ) {
                        throw new ValueError('Invalid properties array in template metadata of property "%s", "properties" is expecting an array', $fullName);
                    }
                    $this->normalizeRecursiveProperties($propertySignature, $fullName);

                    break;
                case self::PROPERTY_TYPE_LIST:
                    if( !isset($propertySignature['items']) ) {
                        throw new ValueError('Missing required items of type list in template metadata of property "%s"', $fullName);
                    }
                    if( !is_array($propertySignature['items']) ) {
                        throw new ValueError('Invalid items array in template metadata of property "%s", "items" is expecting an array', $fullName);
                    }
                    $this->normalizeRecursiveProperties($propertySignature['items'], $fullName . '[]');
                    break;
                // Other types are not expecting any validation
            }
        }
    }


}
