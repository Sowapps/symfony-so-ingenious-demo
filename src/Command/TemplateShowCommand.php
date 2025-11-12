<?php

namespace App\Command;

use App\Service\FragmentService;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:template:show',
    description: 'List fragment templates',
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
            $template->getFile()->getRealPath(),
            'Kind : ' . $template->getKind(),
            'Version : ' . $template->getVersion(),
        ]);

        return Command::SUCCESS;
    }

}
