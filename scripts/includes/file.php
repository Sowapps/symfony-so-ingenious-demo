<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

function download(string $url, string $target): void {
    if( !file_put_contents($target, file_get_contents($url)) ) {
        throw new RuntimeException('Failed to download');
    }
}

/**
 * @param string $path ZIP file path
 * @param string $target Folder target
 * @param string|null $filesRegex Optional filter to restrict extracted files, e.g. 'php_xdebug\-.*\.dll'
 * @return array|null Extracted files or null if all files were extracted
 */
function unzip(string $path, string $target, ?string $filesRegex = null): ?array {
    $archive = new ZipArchive;
    $archive->open($path, ZipArchive::RDONLY);
    $files = null;
    if( $filesRegex ) {
        $files = [];
        for( $i = 0; $i < $archive->numFiles; $i++ ) {
            $fileName = $archive->getNameIndex($i);
            if( preg_match('#' . $filesRegex . '#', $fileName) ) {
                $files[] = $fileName;
            }
        }
    }
    $result = $archive->extractTo($target, $files);
    $archive->close();
    if( !$result ) {
        throw new RuntimeException(sprintf('Failed to unzip file %s to %s', $path, $target));
    }

    return $files;
}

function recursiveRemoveDir(string $path): void {
    if( !is_dir($path) ) {
        throw new InvalidArgumentException(sprintf('Argument $path "%s" must be a directory', $path));
    }
    $dirFiles = scandir($path);
    foreach( $dirFiles as $fileName ) {
        if( $fileName !== '.' && $fileName !== '..' ) {
            $filePath = $path . DIRECTORY_SEPARATOR . $fileName;
            if( is_dir($filePath) && !is_link($path . '/' . $fileName) ) {
                recursiveRemoveDir($filePath);
            } else {
                unlink($filePath);
            }
        }
    }
    rmdir($path);
}

function removePathExtension(string $path): string {
    $info = pathinfo($path);

    return $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'];
}
