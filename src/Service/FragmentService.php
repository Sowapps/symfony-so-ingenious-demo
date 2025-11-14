<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Service;

use App\Entity\Fragment;
use App\Sowapps\SoIngenious\Template;
use DirectoryIterator;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment as Twig;

/**
 * Service to manage fragments
 */
class FragmentService {

    const TEMPLATE_SUFFIX = '.html.twig';

    private readonly SplFileInfo $templateFolder;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Twig                   $twig,
        #[Autowire(param: 'so_ingenious.template.path')]
        string                                  $templatePath,
        #[Autowire(param: 'twig.default_path')]
        private readonly string                 $twigTemplatePath,
    ) {
        $this->templateFolder = new SplFileInfo($templatePath);
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
            $values[$name] = $this->entityManager->getRepository($reference['class'])->find($reference['id']);
        }

        // Convert absolute path to relative path to Twig templates folder
        $path = substr($template->getPath(), strlen($this->twigTemplatePath));
        return $this->twig->render($path, $values);
    }

    public function getTemplate(string $name): Template {
        $fileInfo = new SplFileInfo($this->templateFolder->getRealPath() . '/' . $name . self::TEMPLATE_SUFFIX);
        $templateMeta = $this->extractTemplateMeta($fileInfo);
        return new Template(
            $fileInfo,
            $name,
            $templateMeta['label'],
            $templateMeta['description'],
            $templateMeta['kind'],
            $templateMeta['version']
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
            $list[] = $this->getTemplate($name);
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

        // Normalize
        $meta['kind'] ??= 'fragment';
        $meta['description'] ??= null;
        $meta['version'] ??= 1;

        return $meta;
    }


}
