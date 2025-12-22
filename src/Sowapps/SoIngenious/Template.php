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
        private string $label,
        private string $description,
        private string $kind,
        private ?TemplatePurpose $purpose,
        private int   $version,
        private array $properties,
        private array $children,
        private array $files
    ) {
        // We had to copy it or SplFileInfo is not usable outside this class
        $this->file = new SplFileInfo($file->getRealPath());
    }

    public function getPath(): string {
        return $this->getFile()->getRealPath();
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

    public function getDescription(): string {
        return $this->description;
    }

    public function getKind(): string {
        return $this->kind;
    }

    public function getPurpose(): ?TemplatePurpose {
        return $this->purpose;
    }

    public function getVersion(): int {
        return $this->version;
    }

    public function getProperties(): array {
        return $this->properties;
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function getFiles(): array {
        return $this->files;
    }

}
