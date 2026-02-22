#!/usr/bin/env php
<?php
/**
 * Script to save the log file once a day.
 */

define('CALL_PATH', getcwd());
chdir(__DIR__);

include_once './includes/php.php';
include_once './includes/console.php';

handlePhpErrorsAsExceptions();

// Realpath relative to the call folder
chdir(CALL_PATH);
$userLogPath = $argv[1] ?? 'var/log/dev.log';
$logPath = realpath($userLogPath);
chdir(__DIR__);

if( !is_readable($logPath) ) {
    consoleError(sprintf("Error: Log file '%s' is not readable.", $logPath ?: $userLogPath));
    exit(1);
}

/**
 * Add date before extension
 */
function getTodayFileName(string $logPath): string {
    $info = (object)pathinfo($logPath);
    return $info->dirname . '/' . $info->filename . '.' . date('Ymd') . '.' . $info->extension;
}

$targetPath = getTodayFileName($logPath);

if( file_exists($targetPath) ) {
    writeLn("Already saved logs today.");
    exit(0);
}

/**
 * Add date before extension
 */
function getExistingSuffixedFiles(string $logPath): array {
    $info = (object)pathinfo($logPath);

    return array_map('realpath', glob($info->dirname . '/' . $info->filename . '.*.' . $info->extension));
}

// Remove previous saves
$removePaths = getExistingSuffixedFiles($logPath);
foreach( $removePaths as $path ) {
    unlink($path);
}
writeLn(sprintf("Removed previous log saves : %s", implode(', ', $removePaths)));

// Copy current log file to the new save
writeLn("Moving current log file '$logPath' to '$targetPath'");
rename($logPath, $targetPath);
exit(0);



