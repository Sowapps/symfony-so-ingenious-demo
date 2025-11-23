<?php

namespace App\Command;

use App\Service\FragmentService;
use App\Sowapps\SoIngenious\Template;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:template:show',
    description: 'Show template information',
)]
readonly class TemplateShowCommand {

    public function __construct(
        private FragmentService $fragmentService,
    ) {
    }

    public function __invoke(
        #[Argument('The name of the template.')] string $name,
        OutputInterface $output
    ): int {
        $template = $this->fragmentService->getTemplate($name);
        $output->writeln([
            sprintf('Template "%s" (%s)', $template->getLabel(), $template->getName()),
            $template->getDescription(),
            '',
            $template->getPath(),
            'Kind : ' . $template->getKind(),
            'Version : ' . $template->getVersion(),
            '',
            'Properties : ' . $this->renderTemplateProperties($template),
            '',
            'Children : ' . $this->renderTemplateChildren($template),
        ]);

        return Command::SUCCESS;
    }

    protected function renderTemplateProperties(Template $template): string {
        $properties = $template->getProperties();
        if( !$properties ) {
            return 'None';
        }

        return $this->renderRecursiveArray($properties);
    }

    protected function renderTemplateChildren(Template $template): string {
        $children = $template->getChildren();
        if( !$children ) {
            return 'None';
        }

        return $this->renderRecursiveArray($children);
    }

    protected function renderRecursiveArray(array $array, int $depth = 0): string {
        $indent = str_repeat("  ", $depth);
        $output = '';
        foreach( $array as $key => $value ) {
            $isArray = is_array($value);
            $output .= sprintf('%s%s => %s' . "\n", $indent, $key, $isArray ? '' : $value);
            if( $isArray ) {
                $output .= $this->renderRecursiveArray($value, $depth + 1);
            }
        }

        return $output;
    }

}
