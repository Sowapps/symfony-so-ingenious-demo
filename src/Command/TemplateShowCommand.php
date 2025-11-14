<?php

namespace App\Command;

use App\Service\FragmentService;
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
        ]);

        return Command::SUCCESS;
    }

}
