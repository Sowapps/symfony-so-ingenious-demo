#!/usr/bin/env php
<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

$gitCommands = [
	'update-index --chmod=+x FILE',
];

echo "\nGit common commands\n\n";

foreach( $gitCommands as $subCommand ) {
	$command = sprintf('git %s', $subCommand);
	echo $command . "\n";
}

$composerCommands = [
	'self-update',
	'update',
	'install',
	'recipes',
	'recipes:install --force -v',
];

echo "\nComposer common commands\n\n";

foreach( $composerCommands as $subCommand ) {
	$command = sprintf('composer %s', $subCommand);
	echo $command . "\n";
}

# https://symfony.com/doc/current/frontend/encore/simple-example.html
$yarnCommands = [
	'install',
	'upgrade',
	'encore dev',
	'encore dev --watch',
	'encore production',
	'watch (Use it on DEV)',
	'build (Use it on UAT/PROD)',
];

echo "\nYarn common commands\n\n";

foreach( $yarnCommands as $subCommand ) {
	$command = sprintf('yarn %s', $subCommand);
	echo $command . "\n";
}

$sfCommands = [
	'demo:enterprise:week-timeslot',
	'demo:establishment:generate',
	'cache:clear',
	'doctrine:cache:clear-query',
	'assets:install',
	'make:entity',
	'make:migration',
	'doctrine:migrations:migrate',
	'doctrine:migrations:execute DoctrineMigrations\\\\VersionXXXX --down',
	'doctrine:fixtures:load',
	'context:info',
];

echo "\nSymfony common commands\n\n";

foreach( $sfCommands as $subCommand ) {
	$command = sprintf('php bin/console %s', $subCommand);
	echo $command . "\n";
}

$ourScripts = [
	'remove_last_migration.php --regenerate',
];

echo "\nOur scripts\n\n";

foreach( $ourScripts as $subCommand ) {
	$command = sprintf('./scripts/%s', $subCommand);
	echo $command . "\n";
}
