<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

function getPhpInfoArray(): array {
    ob_start();
    phpinfo(INFO_GENERAL);
    $phpinfo = ob_get_clean();

    //	var_dump($output);

    $info = [];
    preg_match_all('#^(.+) => (.+)$#m', $phpinfo, $matches, PREG_SET_ORDER);

    foreach( $matches as [, $name, $value] ) {
        $key = strtoupper(str_replace(' ', '_', $name));
        $info[$key] = $value;
    }

    return $info;
}

function parseValueList(string $value): array {
    return explode(',', $value);
}

function getPhpInstallInfo(): array {
    $info = getPhpInfoArray();
    [, $buildTS, $buildCompiler] = parseValueList($info['PHP_EXTENSION_BUILD']);

    $phpPath = dirname(php_ini_loaded_file());
    $phpExtPath = $phpPath . DIRECTORY_SEPARATOR . ini_get('extension_dir');

    return [
        // https://www.php.net/manual/fr/reserved.constants.php
        'version'         => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
        'architecture'    => PHP_INT_SIZE === 8 ? 'x64' : 'x86',
        'threadSafe'      => ZEND_THREAD_SAFE,
        'buildThreadSafe' => strtolower($buildTS),
        'buildCompiler'   => strtolower($buildCompiler),
        'phpPath'         => $phpPath,
        'extensionPath'   => $phpExtPath,
    ];
}

function handlePhpErrorsAsExceptions(): void {
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });
}
