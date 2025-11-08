# So Ingenious Demo

## Php Version

Php version is limited by production server (Ubuntu 20.04).  
The production server should be upgraded to use a more recent version of Php.

## Local dev environment

Set .php-version to define PHP version served by Symfony server.  
Use `nano ~/.bashrc` to change you Cli Php version.

echo "# Php environment setup
alias php82="/c/tools/php82/php.exe"
alias php84="/c/tools/php84/php.exe"
alias php=php82
" > ~/.bashrc
