<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Php;

use RuntimeException;
use SplFileObject;

class PhpConfigFile {

    const string DEFAULT_SKELETON_NAME = 'php.ini-development';

    private SplFileObject $configFile;

    private SplFileObject $skeletonFile;

    private string $appConfigStart = '; ### APP CONFIG / START ###';

    private string $appConfigEnd = '; ### APP CONFIG / END ###';

    private string $contents;

    /**
     * PhpConfigFile constructor
     *
     * @param string $configPath
     * @param string|null $skeletonPath
     */
    public function __construct(string $configPath, ?string $skeletonPath = null) {
        $this->configFile = new SplFileObject($configPath, 'r+');
        $skeletonPath ??= dirname($configPath) . '/' . self::DEFAULT_SKELETON_NAME;
        $this->skeletonFile = new SplFileObject($skeletonPath, 'r');

        $this->initialize();
    }

    public function initialize(): void {
        if( !$this->configFile->isFile() ) {
            $this->copySkeleton($this->skeletonFile, $this->configFile);
        }

        $this->contents = file_get_contents($this->configFile->getRealPath());
    }

    protected function copySkeleton(SplFileObject $from, SplFileObject $to): bool {
        return copy($from->getRealPath(), $to->getRealPath());
    }

    /**
     * @param string $text
     * @return $this
     * @warning Currently, we ignore any other declaration of these settings in file
     */
    public function setAppConfiguration(string $text): static {
        $regex = '%^' . $this->escapePcreRegexValue($this->appConfigStart) . '\n.+\n' . $this->escapePcreRegexValue($this->appConfigEnd) . '$%sm';
        $section = <<<CONFIG
$this->appConfigStart
$text
$this->appConfigEnd
CONFIG;
        $phpIniContents = $this->contents;
        // Replace existing app configuration with the new one
        $phpIniContents = preg_replace($regex, $this->escapePcreReplaceValue($section), $phpIniContents, 1, $replacementCount);
        if( !$replacementCount ) {
            // Currently, there is no app config in php ini file, so we add it to the end
            $phpIniContents .= "\n\n$section\n\n";
        }
        $this->contents = $phpIniContents;

        return $this;
    }

    protected function escapePcreRegexValue(string $value): string {
        return preg_quote($value, '%');
    }

    protected function escapePcreReplaceValue(string $value): string {
        return str_replace('$', '\\$', $value);
    }

    public function save(): static {
        $result = file_put_contents($this->configFile->getRealPath(), $this->contents);
        if( !$result ) {
            throw new RuntimeException(sprintf('Unable to write to file %s', $this->configFile->getRealPath()));
        }

        return $this;
    }

}
