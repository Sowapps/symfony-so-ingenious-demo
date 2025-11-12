<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Command;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\Migrations\Version\Direction;
use Doctrine\Migrations\Version\Version;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Class MigrationRemoveLastCommand
 *
 * @package App\Command
 *
 * Add this to your services.yaml
 * services:
 *  ...
 *    Doctrine\Migrations\DependencyFactory: '@doctrine.migrations.dependency_factory'
 */
#[AsCommand(
    name: 'migrations:remove-latest',
    description: 'Remove latest doctrine applied migration',
)]
class MigrationRemoveLastCommand extends Command {
	protected StyleInterface $io;
	private DependencyFactory $dependencyFactory;
	private string $projectPath;

	public function __construct(
        #[Autowire(service: 'doctrine.migrations.dependency_factory')]
        DependencyFactory $dependencyFactory,
        #[Autowire(param: 'kernel.project_dir')]
        string $projectPath
    ) {
		parent::__construct();

		$this->dependencyFactory = $dependencyFactory;
		$this->projectPath = $projectPath;
	}

	protected function configure(): void {
		$this
			->setDescription('Remove latest doctrine applied migration')
			->setHelp('Remove latest doctrine applied migration, migration is not applied then.')
			->addOption('write-sql', null, InputOption::VALUE_OPTIONAL, 'The path to output the migration SQL file. Defaults to current working directory.', false)
			->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the migration as a dry run.');
	}

	protected function initialize(InputInterface $input, OutputInterface $output): void {
		$this->io = new SymfonyStyle($input, $output);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {

		$aliasResolver = $this->dependencyFactory->getVersionAliasResolver();

		$currentVersion = $aliasResolver->resolveVersionAlias('current');
		$latestVersion = $aliasResolver->resolveVersionAlias('latest');
		$latestVersionIsApplied = $currentVersion->equals($latestVersion);

		$migratorConfigurationFactory = $this->getDependencyFactory()->getConsoleInputMigratorConfigurationFactory();
		$migratorConfiguration = $migratorConfigurationFactory->getMigratorConfiguration($input);

		$question = sprintf(
			'WARNING! You are about to undo migration "%s" and to remove migration file. Are you sure you wish to continue?',
			$latestVersion
		);
		if( !$migratorConfiguration->isDryRun() && !$this->canExecute($question, $input) ) {
			$this->io->error('Operation cancelled!');

			return 1;
		}

		chdir($this->projectPath);

		$latestVersionPath = sprintf('migrations/%s.php', str_replace('DoctrineMigrations\\', '', $latestVersion));

		if( !is_writable($latestVersionPath) ) {
			$this->io->error(sprintf('Unable to delete latest version "%s" file, file is not part of application or application has no right to remove it. Look at "%s"', $latestVersion, $latestVersionPath));

			return 1;
		}

		if( $latestVersionIsApplied ) {
			$this->io->text('Undo latest version');
			$this->undoLatestVersion($input, $latestVersion, $migratorConfiguration);
		} else {
			$this->io->text(sprintf("Latest version \"%s\" is not applied, so we don't revert it but we remove it.", $latestVersion));
		}

		$this->removeVersion($latestVersionPath);

		$this->io->success(sprintf('Removed latest version %s.', $latestVersion));

		return 0;
	}

	protected function getDependencyFactory(): DependencyFactory {
		return $this->dependencyFactory;
	}

	protected function canExecute(string $question, InputInterface $input): bool {
		return !$input->isInteractive() || $this->io->confirm($question);
	}

	protected function undoLatestVersion(InputInterface $input, Version $version, MigratorConfiguration $migratorConfiguration): void {
		$direction = Direction::DOWN;

		// Then based on \Doctrine\Migrations\Tools\Console\Command\ExecuteCommand

		$this->getDependencyFactory()->getMetadataStorage()->ensureInitialized();

		// ... Gap ...

		$path = $input->getOption('write-sql') ?? getcwd();

		// ... Gap ...

		$planCalculator = $this->getDependencyFactory()->getMigrationPlanCalculator();
		$plan = $planCalculator->getPlanForVersions([$version], $direction);// Modified here $version

		$this->getDependencyFactory()->getLogger()->notice(
			'Executing' . ($migratorConfiguration->isDryRun() ? ' (dry-run)' : '') . ' {versions} {direction}',
			[
				'direction' => $plan->getDirection(),
				'versions'  => $version,// Modified here $version
			]
		);

		$migrator = $this->getDependencyFactory()->getMigrator();
		$sql = $migrator->migrate($plan, $migratorConfiguration);

		if( is_string($path) ) {
			$writer = $this->getDependencyFactory()->getQueryWriter();
			$writer->write($path, $direction, $sql);
		}

	}

	protected function removeVersion(string $versionPath): bool {
		return unlink($versionPath);
	}

}
