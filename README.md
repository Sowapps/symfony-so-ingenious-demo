# So Ingenious Demo

## Php Version

Php version is limited by production server (Ubuntu 20.04).  
The production server should be upgraded to use a more recent version of Php.

Current PHP version is 8.2 (> 8.2.0)

## Fixtures

Some features are required to initialize the app with basic data, this is why the package `doctrine/doctrine-fixtures-bundle` is not dev only.  
For initialization fixtures : `config/fixtures/fixtures-init.yaml`  
For demo sample fixtures : `config/fixtures/fixtures-sample.yaml`  

Import with
```php
bin/console doctrine:fixtures:load
```

## Development

This project is partially based on SLG (So Lil' Game)

## Local dev environment

Set .php-version to define PHP version served by Symfony server.  
Use `nano ~/.bashrc` to change you Cli Php version.

echo "# Php environment setup
alias php82="/c/tools/php82/php.exe"
alias php84="/c/tools/php84/php.exe"
alias php=php82
" > ~/.bashrc
