#!/usr/bin/env php
<?php
/**
 * Script to install and configure the docker container
 * @example ./scripts/docker_install.php dev
 */

include_once __DIR__ . '/includes/php.php';
include_once __DIR__ . '/includes/console.php';

handlePhpErrorsAsExceptions();

$mode = $argv[1] ?? '';
$options = getopt('i', ['interactive']);

$devMode = match (strtolower($mode)) {
    'dev' => true,
    'prod' => false,
    default => throw new ValueError('First argument must be either "dev" or "prod".'),
};

$interactive = isset($options['i']) || isset($options['interactive']);

/**
 * Run commands on docker container
 */
readonly class DockerRunner {
    public function __construct(
        private string $container,
        private string $configPath,
        private bool   $verbose = false,
        private bool   $dryRun = false,
    ) {
    }

    public function run(string $containerCommand, ?string $user = null): bool {
        // Build command
        $command = sprintf('docker-compose -f %s exec %s %s %s', $this->configPath, $user ? '--user ' . $user : '', $this->container, $containerCommand);

        // Display command
        if( $this->verbose ) {
            printf("%s on %s : %s\n", $this->dryRun ? 'Simulate' : 'Run', $this->container, $command);
        }

        // Process command
        if( $this->dryRun ) {
            return true;
        }
        $result = passthru($command);

        return $result === null;
    }
}

$runner = new DockerRunner('web', 'docker/docker-compose.yml', verbose: true, dryRun: false);

$composerMode = '--no-dev';
$modeText = 'PROD';
if( $devMode ) {
    $composerMode = '';
    $modeText = 'DEV';
}

echo "Installing composer dependencies ($modeText)...\n";
$runner->run(sprintf('composer install %s', $composerMode), user: 'www-data');
echo "\n";

if( $devMode ) {
    echo "Creating database...\n";
    $runner->run("php bin/console doctrine:database:create");
    echo "\n";
}

echo "Migrating database to latest version...\n";
$runner->run(sprintf('php bin/console %s doctrine:migrations:migrate', $interactive ? '' : '--no-interaction'));
echo "\n";

echo "Installing initialization fixtures...\n";
$runner->run('php bin/console doctrine:fixtures:load');
echo "\n";
// Cannot let it non-interactive as it could reset the database, and this script must be idempotent

echo "Installing asset dependencies with AssetMapper...\n";
$runner->run('php bin/console importmap:install');
echo "\n";

// Additional setup for production mode
if( !$devMode ) {
    echo "Compiling assets for production...\n";
    $runner->run('php bin/console asset-map:compile');
    echo "\n";
}

echo "Setup completed successfully.\n";

