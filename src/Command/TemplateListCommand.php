<?php

namespace App\Command;

use App\Service\FragmentService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:template:list',
    description: 'List fragment templates',
)]
class TemplateListCommand extends Command {

    public function __construct(
        private readonly FragmentService $fragmentService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);

        $templates = $this->fragmentService->listTemplates();
        foreach($templates as $template) {
            $io->writeln(sprintf('Template "%s" (%s) : %s', $template->getLabel(), $template->getName(), $template->getFile()->getRealPath()));
        }

        return Command::SUCCESS;
    }

}
