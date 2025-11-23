<?php

namespace App\Command;

use App\Service\FragmentService;
use App\Sowapps\SoIngenious\TemplatePurpose;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function __invoke(
        OutputInterface                                                        $output,
        #[Option(suggestedValues: [TemplatePurpose::class, 'values'])] ?string $purpose = null
        //        #[Option] ?TemplatePurpose $purpose = null // TODO Symfony 7.4
    ): int {
        if( $purpose ) {
            $purpose = TemplatePurpose::from($purpose);
            $templates = $this->fragmentService->listTemplatesByPurpose($purpose);
        } else {
            $templates = $this->fragmentService->listTemplates();
        }

        foreach($templates as $template) {
            $output->writeln(sprintf('Template "%s" (%s) : %s', $template->getLabel(), $template->getName(), $template->getPath()));
        }

        return Command::SUCCESS;
    }

}
