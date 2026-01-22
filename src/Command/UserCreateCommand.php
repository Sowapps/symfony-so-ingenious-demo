<?php

namespace App\Command;

use App\Entity\User;
use App\Exception\ConstraintValidationException;
use App\Service\EntityService;
use Sowapps\SoCore\Service\SecurityService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create a brand new user',
)]
class UserCreateCommand extends Command {

    public function __construct(
        private readonly EntityService               $entityService,
        private readonly SecurityService             $securityService,
		private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->addOption('weak', 'w', InputOption::VALUE_NONE, 'Apply weak security validation for password')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run mode')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'User email')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'User name')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'User password')
            ->addOption('roles', null, InputOption::VALUE_REQUIRED, 'User roles')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $allowWeakPassword = $input->getOption('weak');
        $dryRun = $input->getOption('dry-run');
        $userEmail = $input->getOption('email');
        $userName = $input->getOption('name');
        $userPassword = $allowWeakPassword ? $input->getOption('password') : null;// Unsecure (but locally we don't care)
        $userRoles = $input->getOption('roles');

		$this->entityService->setDryRun($dryRun);

        $user = new User();
        do {
            $user->setEmail($userEmail ?? $io->ask('User email address', $user->getEmail()));
            $user->setName($userName ?? $io->ask('User displayed name', $user->getName()));

            $password = $userPassword ?? $io->askHidden('User password', Validation::createCallable($allowWeakPassword ?
                new Assert\Length([
                    'min' => 4,
                ]) :
                new Assert\PasswordStrength([
                    'minScore' => Assert\PasswordStrength::STRENGTH_MEDIUM,
                ])));
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $roles = $userRoles ?? $io->ask(
                'All roles : ' . implode(', ', $this->securityService->getAllRoles()) . "\n" .
                ' User roles (coma separated)', implode(',', $user->getRoles()));
            $user->setRoles(array_map('trim', explode(',', $roles)));

            $valid = false;
            try {
                $this->entityService->validate($user);
                $valid = true;
			} catch( ConstraintValidationException $exception ) {
                foreach( $exception->getErrors() as $error ) {
                    $io->error(sprintf('Validation error : %s', $error->getMessage()));
                }
            }
        } while( !$valid );

		// Save user
		$this->entityService->create($user);

		$this->entityService->flush();
		if( $dryRun && $io->isVerbose() ) {
			$io->text('Dry run, no changes applied');
		}

        $io->success(sprintf('New user #%s created.', $user->getId() ?? 'NA'));

        return Command::SUCCESS;
    }

}
