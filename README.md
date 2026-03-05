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

This project is partially based on SLG (So Lil' Game).  
For local development, the project is using a Docker container for database, web server... see [docker-compose.yml](docker/docker-compose.yml).

## Configure local host

Requires Docker on WSL 2, you can use Docker Desktop on Windows.  

Set .php-version to define PHP version served by Symfony server.  
Use `nano ~/.bashrc` to change you Cli Php version.

echo "# Php environment setup
alias php82="/c/tools/php82/php.exe"
alias php84="/c/tools/php84/php.exe"
alias php=php82
" > ~/.bashrc

#### Configure DNS server

Add `127.0.0.1 so-ingenious.local` to your DNS server.  
On Windows, you can add it in `C:\Windows\System32\drivers\etc\hosts`.  
Personally, I prefer to use Acrylic UI.  
The whole docker configuration is made to use this domain name.

## Configure PhpStorm

Go to PhpStorm: Settings → PHP → Servers

Add server "so-ingenious.local"
    Host : so-ingenious.local
    Port : 8443
    Debugger : Xdebug
    ✅ Use path mappings

Add path mappings:

| File/Directory (local)                                                           | Absolute path on the server            |
| -------------------------------------------------------------------------------- | -------------------------------------- |
| `\\wsl.localhost\Ubuntu-24.04\home\sowapps\projects\symfony-so-core-bundle`      | `/var/www/symfony-so-core-bundle`      |
| `\\wsl.localhost\Ubuntu-24.04\home\sowapps\projects\symfony-so-ingenious-bundle` | `/var/www/symfony-so-ingenious-bundle` |
| `\\wsl.localhost\Ubuntu-24.04\home\sowapps\projects\symfony-so-ingenious-demo`   | `/var/www/html`                        |
| `\\wsl.localhost\Ubuntu-24.04\home\sowapps\projects\symfony-so-log-bundle`       | `/var/www/symfony-so-log-bundle`       |

