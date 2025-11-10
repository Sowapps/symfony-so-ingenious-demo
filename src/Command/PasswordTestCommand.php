<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints as Assert;

#[AsCommand(
	name: 'app:password:eval',
	description: 'Evaluate password strength',
)]
class PasswordTestCommand extends Command {
	
	protected function configure(): void {
		$this
			->addArgument('password', InputArgument::OPTIONAL, 'Password to evaluate strength');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);
		
		$password = $input->getArgument('password') ?? $io->askHidden('Password (hidden)');
		
		$strength = Assert\PasswordStrengthValidator::estimateStrength($password);
		
		$io->text(sprintf('Password is %s (%d / 4)', $this->getStrengthText($strength), $strength));
		
		return Command::SUCCESS;
	}
	
	protected function getStrengthText(int $strength): string {
		return match ($strength) {
			Assert\PasswordStrength::STRENGTH_VERY_WEAK => 'insecure',
			Assert\PasswordStrength::STRENGTH_WEAK => 'weak',
			Assert\PasswordStrength::STRENGTH_MEDIUM => 'acceptable',
			Assert\PasswordStrength::STRENGTH_STRONG => 'strong',
			Assert\PasswordStrength::STRENGTH_VERY_STRONG => 'very strong',
		};
	}
	
}
