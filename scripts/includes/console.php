<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

/**
 * Outputs an error message to the standard error stream (stderr).
 *
 * This function is designed to be used in PHP CLI scripts to properly
 * direct error messages to the standard error stream. This is useful
 * for separating error messages from standard output, especially when
 * redirecting output in shell scripts or logging.
 *
 * @param string $text The error message to be output.
 * @param int|null $exitCode The result code (do not exit if null)
 * @return void
 */
function consoleError(string $text, ?int $exitCode = null): void {
    // Écrire le message d'erreur dans le flux d'erreur standard (stderr)
    fwrite(STDERR, "\033[31m" . $text . "\033[0m" . PHP_EOL);

    // Si un code de sortie est fourni, arrêter le script avec ce code
    if( $exitCode !== null ) {
        exit($exitCode);
    }
}

function writeLn(string $text): void {
    echo $text . PHP_EOL;
}
