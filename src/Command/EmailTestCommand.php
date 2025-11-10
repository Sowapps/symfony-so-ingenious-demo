<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Command;

use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(
    name: 'email:send-test',
    description: 'Send a test email',
)]
class EmailTestCommand extends Command {

    const DEFAULT_RECIPIENT = 'contact@sowapps.com';

    public function __construct(
        private readonly EmailService    $emailService,
        private readonly RouterInterface $router
    ) {
        $this->router->getContext()->setParameter('_locale', 'en');

        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->addArgument('recipientEmail', InputArgument::OPTIONAL, 'Recipient email', self::DEFAULT_RECIPIENT);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $recipientEmail = $input->getArgument('recipientEmail');

        $io = new SymfonyStyle($input, $output);

        $this->emailService->sendTestEmail($recipientEmail);

        $io->success(sprintf('Email queued to deliver to %s', $recipientEmail));

        return 0;
    }

}
