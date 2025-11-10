<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Command;

use App\Core\Command\AbstractCommand;
use App\Core\Session\CliSessionStartable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
	name: 'app:session:show',
	description: 'Show current session',
	aliases: ['app:session'],
)]
class SessionShowCommand extends AbstractCommand implements CliSessionStartable {
	
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);
		
		$io->text('Session name : ' . $this->session->getName());
		$io->text('Session id : ' . $this->session->getId());
		
		$user = $this->getUser();
		$io->text('User : ' . ($user ? $user->getLabel() : 'Not authenticated'));
		
		$clientSession = $this->getClientSession();
		$io->text('Client Session : ' . ($clientSession ? '#' . $clientSession->getId() : 'No client session'));
		
		return 0;
	}
	
}
