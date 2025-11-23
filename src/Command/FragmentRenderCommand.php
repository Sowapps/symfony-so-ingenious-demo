<?php

namespace App\Command;

use App\Repository\FragmentRepository;
use App\Service\FragmentService;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fragment:render',
    description: 'Render fragment from template',
)]
readonly class FragmentRenderCommand {

    public function __construct(
        private FragmentService    $fragmentService,
        private FragmentRepository $fragmentRepository,
    ) {
    }

    public function __invoke(
        #[Argument('The fragment ID.')] int $fragmentId,
        OutputInterface                     $output
    ): int {
        $fragment = $this->fragmentRepository->find($fragmentId);
        $render = $this->fragmentService->renderFragment($fragment);
        $output->writeln($render);

        return Command::SUCCESS;
    }

}
