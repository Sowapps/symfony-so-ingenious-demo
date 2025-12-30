<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use App\Entity\Fragment;
use App\Entity\PublicationFragment;
use App\Repository\FragmentRepository;
use App\Repository\PublicationFragmentRepository;
use App\Repository\SlotFragmentRepository;
use App\Sowapps\SoIngenious\QueryCriteria;
use App\Sowapps\SoIngenious\Template;
use App\Sowapps\SoIngenious\TemplatePurpose;
use DirectoryIterator;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\QueryException;
use RuntimeException;
use Sowapps\SoCore\Service\LanguageService;
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
    /**
     * @see QueryCriteria
     */
    public const PROPERTY_TYPE_QUERY_CRITERIA = 'query_criteria';
    public const PROPERTY_TYPE_OBJECT = 'object';// Require "properties" in signature
    public const PROPERTY_TYPE_LIST = 'list';// Require "items" in signature

    const TEMPLATE_SUFFIX = '.html.twig';

    private readonly SplFileInfo $templateFolder;

    public function __construct(
        #[Autowire(service: 'app.fragment')]
        private readonly TagAwareCacheInterface        $cache,
        private readonly Twig                          $twig,
        private readonly EntityService                 $entityService,
        private readonly LanguageService               $languageService,
        private readonly FragmentRepository            $fragmentRepository,
        private readonly PublicationFragmentRepository $publicationFragmentRepository,
        private readonly SlotFragmentRepository        $slotFragmentRepository,
        #[Autowire(param: 'so_ingenious.template.path')]
        string                                         $templatePath,
        #[Autowire(param: 'twig.default_path')]
        private readonly string                        $twigTemplatePath,
    ) {
        $this->templateFolder = new SplFileInfo($templatePath);
    }

    /**
     * @param QueryCriteria $itemCriteria
     * @return PublicationFragment[]
     * @throws QueryException
     */
    public function getCriteriaItems(QueryCriteria $itemCriteria): array {
        // TODO Clean code
        // , ?string $itemPurpose = null
        // Build Doctrine Criteria
        $criteria = Criteria::create();
        if( $itemCriteria->getOrderBy() ) {
            // Optional for listing all paths
            $criteria->orderBy($itemCriteria->getOrderBy());
        }
        if( $itemCriteria->getLimit() !== null ) {
            $criteria->setMaxResults($itemCriteria->getLimit());
        }
        $listFilters = $this->publicationFragmentRepository->getListFilters();
        $query = $this->publicationFragmentRepository->query();
        $qe = $query->expr();
        //        $eb = Criteria::expr();
        $conditionAndX = $qe->andX();
        //        if($itemPurpose) {
        //            $criteria->andWhere($eb->eq('purpose', $itemPurpose));
        //        }

        foreach( $itemCriteria->getFilters() as $filterName => $filterValue ) {
            $filterSelect = $listFilters[$filterName] ?? null;
            if( !$filterSelect ) {
                throw new RuntimeException(sprintf('Filter "%s" not found for a route', $filterName));
            }
            $conditionAndX->add($qe->eq($filterSelect, ':' . $filterName));
            $query->setParameter($filterName, $filterValue);
        }

        $query = $query->addCriteria($criteria);
        if( $conditionAndX->count() ) {
            // Add complex filters
            $query->andWhere($conditionAndX);
        }

        $items = $query
            ->getQuery()
            ->getResult();
        //        dump($items);
        return $items;
    }

    public function getBySelector(string $selector, string $value): ?Fragment {
        return match ($selector) {
            'id' => $this->fragmentRepository->find($value),
            'slot' => $this->getSlotFragment($value),
        };
    }

    public function getSlotFragment(string $slot): ?Fragment {
        $language = $this->languageService->getActiveLanguage();
        $slotFragment = $this->slotFragmentRepository->getByName($slot);

        return $this->fragmentRepository->getByLocalizedUnitAndLanguage($slotFragment->getFragmentUnit(), $language);
    }

    public function getFragmentRendering(Fragment $fragment, array $parameters = []): string {
        return $this->renderFragment($fragment, $parameters);// Disable cache

        //        $key = $this->getFragmentCacheKey($fragment);
        //        return $this->cache->get($key, function (ItemInterface $item) use ($fragment) {
        //            $item->tag(['fragment', 'fragment_' . $fragment->getId(), 'language_' . $fragment->getLanguage()->getId()]);
        //            $item->expiresAfter(86400);
        //            return $this->renderFragment($fragment, $parameters);
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

    public function renderFragment(Fragment $fragment, array $values = []): string {
        $template = $this->getTemplate($fragment->getTemplateName());
        $values['template'] = $template;
        $values['fragment'] = $fragment;
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
            isset($templateMeta['purpose']) ? TemplatePurpose::from($templateMeta['purpose']) : null,
            $templateMeta['version'],
            $templateMeta['properties'],
            $templateMeta['children'],
            $templateMeta['files']
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
     * @return Template[]
     */
    public function listTemplatesByPurpose(TemplatePurpose $purpose): array {
        return array_filter($this->scanTemplates($this->templateFolder), fn(Template $template) => $template->getPurpose() === $purpose);
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
                    ...$this->scanTemplates($fileInfo, ($prefix ? $prefix . '/' : '') . $fileInfo->getFilename()),
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

        $this->normalizeRecursiveProperties($meta, '');

        // Parameters are never required
        $meta['parameters'] ??= [];
        $this->normalizeParameters($meta['parameters']);

        // Normalize children
        // All children are required
        $meta['children'] ??= [];
        foreach( $meta['children'] as $childName => &$childSignature ) {
            $isMultiple = false;
            if( is_string($childSignature) ) {
                $isMultiple = $childSignature[0] === '*';
                $childSignature = ['purpose' => ltrim($childSignature, '*')];
            } else if( !isset($childSignature['purpose']) ) {
                throw new ValueError(sprintf('Missing required purpose in signature of child "%s"', $childName));
            }
            $childSignature['multiple'] = $isMultiple;
            $childSignature['required'] = true;
        }
        unset($childName, $childSignature);

        // Normalize files
        $meta['files'] ??= [];
        foreach( $meta['files'] as $fileName => &$fileSignature ) {
            $isMultiple = false;
            $isRequired = true;
            if( is_string($fileSignature) ) {
                $isOptional = $fileSignature[0] === '?';
                $isRequired = !$isOptional;
                $fileSignature = ltrim($fileSignature, '?');
                $isMultiple = $fileSignature[0] === '*';
                $fileSignature = ltrim($fileSignature, '*');
                [$purpose, $mimeType] = explode('=', $fileSignature, 2);
                $fileSignature = [
                    'purpose'  => $purpose,
                    'mimeType' => $mimeType,
                ];
            } else if( empty($fileSignature['purpose']) ) {
                throw new ValueError(sprintf('Missing required purpose in signature of file "%s"', $fileName));
            } else if( empty($fileSignature['mimeType']) ) {
                throw new ValueError(sprintf('Missing required mimeType in signature of file "%s"', $fileName));
            }
            $fileSignature['multiple'] = $isMultiple;
            $fileSignature['required'] = $isRequired;
        }
        unset($fileName, $fileSignature);

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

    protected function normalizeParameters(array &$object): void {
        foreach( $object as &$propertySignature ) {
            // If signature is a string, convert it into array
            if( is_string($propertySignature) ) {
                $propertySignature = ['type' => ltrim($propertySignature, '?')];
            }
            // Now signature is an array

            $propertySignature['required'] = false;
        }
    }


}
