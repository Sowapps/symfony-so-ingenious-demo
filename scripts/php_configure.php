#!/usr/bin/env php
<?php
/**
 * Script to configure php.ini settings for cURL and OpenSSL.
 * If php.ini is already configured with cacert.pem missing, https request won't work, and it won't be able to download it (use uninstall or remove manual configuration).
 */

include_once __DIR__ . '/src/Php/PhpConfigFile.php';
include_once __DIR__ . '/src/includes/php.php';
include_once __DIR__ . '/src/includes/console.php';

handlePhpErrorsAsExceptions();

$localCaCertPath = __DIR__ . '/ressources/php/cacert.pem';

$options = getopt('', ['uninstall']);

$uninstall = isset($options['uninstall']);

// Get the path to the loaded php.ini file
$phpIniPath = php_ini_loaded_file();

if( $phpIniPath === false ) {
    echo "php.ini file not found.\n";
    exit(1);
}

echo "Loaded php.ini: $phpIniPath\n";

// Deduce the PHP directory from the php.ini path
$phpDir = str_replace('\\', DIRECTORY_SEPARATOR, dirname($phpIniPath));

// Define the paths for cacert.pem
$cacertPath = $phpDir . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'cacert.pem';
$cacertDir = dirname($cacertPath);

$phpConfig = new Php\PhpConfigFile($phpIniPath);

if( !$uninstall ) {
    if( !file_exists($cacertPath) ) {
        try {
            // First try to download new CA file, but we use an SSL connection, so we use it only to update if this is possible
            file_put_contents($cacertPath, file_get_contents('https://curl.se/ca/cacert.pem'));
            echo 'Downloaded file cacert.pem' . PHP_EOL;
        } catch( ErrorException $exception ) {
            consoleError('Error downloading CA Cert file : ' . $exception->getMessage());
            copy($localCaCertPath, $cacertPath);
            echo 'Used project file cacert.pem' . PHP_EOL;
        }
    }
} else {
    if( file_exists($cacertPath) ) {
        unlink($cacertPath);
        echo 'Removed file cacert.pem' . PHP_EOL;
    }
}

// Added extensions and settings
$rawSettings = $uninstall ? PHP_EOL : <<<CONFIG
extension_dir = "ext"

extension=curl
extension=intl
extension=mbstring
extension=openssl
extension=php_fileinfo
extension=pdo_mysql
extension=zip

zend_extension=xdebug
xdebug.mode=debug

[curl]
curl.cainfo=$cacertPath

[openssl]
openssl.cafile=$cacertPath
openssl.capath=$cacertDir

CONFIG;

$phpConfig
    ->setAppConfiguration($rawSettings)
    ->save();

printf('Configuration file php.ini updated successfully to %s app configuration.' . PHP_EOL, $uninstall ? 'uninstall' : 'install');
