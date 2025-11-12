<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Sowapps\SoIngenious;

use SplFileInfo;

readonly class Template {

    private SplFileInfo $file;

    public function __construct(
        SplFileInfo    $file,
        private string $name,
        private string $label
    ) {
        // We had to copy it or SplFileInfo is not usable outside this class
        $this->file = new SplFileInfo($file->getRealPath());
    }

    public function getFile(): SplFileInfo {
        return $this->file;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLabel(): string {
        return $this->label;
    }

}
