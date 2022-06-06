#!/usr/bin/env php
<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

chdir(__DIR__ . '/..');

function getInputOptions($options): array {
	$longOptions = [];
	foreach( $options as $longOption => $optionConfig ) {
		$longOptions[] = $longOption;
	}
	$values = getopt('', $longOptions);
	foreach( $options as $longOption => $optionConfig ) {
		$values[$longOption] = isset($values[$longOption]) ? ($values[$longOption] ?: true) : false;
	}
	
	return $values;
}

class Command {
	
	private string $phpBin = '/usr/bin/php7.4';
	
	public function run($command) {
		passthru(sprintf('%s bin/console %s --ansi', $this->phpBin, $command), $result);
		
		return !$result;
	}
	
}

$sfCommand = new Command();

$options = getInputOptions(['regenerate' => []]);

$removed = $sfCommand->run('migrations:remove-latest');

if( $removed && $options['regenerate'] ) {
	echo "Generating new migration\n";
	$sfCommand->run('make:migration');
	echo "\nApplying new migration\n";
	$sfCommand->run('doctrine:migrations:migrate');
}
