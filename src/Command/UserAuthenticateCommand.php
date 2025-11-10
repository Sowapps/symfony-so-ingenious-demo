<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Command;

use App\Core\Command\AbstractCommand;
use App\Core\Session\CliSessionStartable;
use App\Repository\UserRepository;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
	name: 'app:authenticate',
	description: 'Authenticate user into the current session',
	aliases: ['app:auth'],
)]
class UserAuthenticateCommand extends AbstractCommand implements CliSessionStartable {
	
	public function __construct(
		private readonly UserRepository $userRepository
	) {
		parent::__construct();
	}
	
	protected function configure(): void {
		$this
			->addArgument('userId', InputArgument::OPTIONAL, 'User id to get authenticated')
			->addOption('logout', null, InputOption::VALUE_NONE, 'Log out current authenticated user');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);
		
		$logout = $input->getOption('logout');
		if( $logout ) {
			return $this->revokeAuthentication($io);
		}
		
		$userId = $input->getArgument('userId');
		
		if( !$userId ) {
			throw new InvalidArgumentException('User id is required');
		}
		if( !ctype_digit($userId) ) {
			throw new InvalidArgumentException('User id must be an integer');
		}
		
		$user = $this->userRepository->find($userId);
		if( !$user ) {
			throw new InvalidArgumentException('No user found with this id');
		}
		
		$this->setUser($user);
		
		$io->success(sprintf('Authenticated as %s', $user->getLabel()));
		
		return Command::SUCCESS;
	}
	
	protected function revokeAuthentication(SymfonyStyle $io): int {
		if( !$this->getUser() ) {
			$io->warning('Not authenticated, nothing was done');
			
			return Command::INVALID;
		}
		$clientSessionDisconnected = $this->disconnectUser();
		
		$io->success(sprintf('Authentication revoked %s', $clientSessionDisconnected ? 'and client session reset' : ''));
		
		return Command::SUCCESS;
	}
	
}
