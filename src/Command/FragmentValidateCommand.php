<?php

namespace App\Command;

use App\Entity\Fragment;
use App\Repository\FragmentRepository;
use App\Service\FragmentService;
use App\Sowapps\SoIngenious\Template;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use ValueError;

#[AsCommand(
    name: 'app:fragment:validate',
    description: 'Validate that all fragments are matching the signature ot their templates',
)]
class FragmentValidateCommand extends Command {

    public function __construct(
        private readonly FragmentService    $fragmentService,
        private readonly FragmentRepository $fragmentRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);

        $templates = $this->fragmentService->listTemplates();
        /** @var Fragment[] $fragments */
        $fragments = $this->fragmentRepository->findAll();
        $validCount = $invalidCount = 0;
        /**
         * Invalid if any error but warning does not count
         */

        foreach( $fragments as $fragment ) {
            try {
                [$errors, $warnings] = $this->getFragmentErrors($fragment, $templates[$fragment->getTemplateName()] ?? null);
                if( $warnings ) {
                    $io->writeln(array_map(fn($message) => 'WARNING: ' . $message, $warnings));
                }
                if( $errors ) {
                    $io->writeln(array_map(fn($message) => 'ERROR: ' . $message, $errors));
                    $io->writeln(sprintf('Fragment "%s" #%s with template "%s" has errors', $fragment->getName(), $fragment->getId(), $fragment->getTemplateName()));
                    $invalidCount++;
                } else {
                    $validCount++;
                    $io->writeln(sprintf('Fragment "%s" #%s with template "%s" is valid', $fragment->getName(), $fragment->getId(), $fragment->getTemplateName()), OutputInterface::VERBOSITY_VERBOSE);
                }
            } catch( Throwable $exception ) {
                $invalidCount++;
                $io->warning(sprintf('Error with fragment "%s" #%s : %s', $fragment->getName(), $fragment->getId(), $exception->getMessage()));
            }
        }

        $io->info(sprintf('Summary : %d valid fragments against %d invalid ones.', $validCount, $invalidCount));

        return Command::SUCCESS;
    }

    /**
     * @param Fragment $fragment
     * @param Template|null $template
     * @return array<string[]>
     */
    private function getFragmentErrors(Fragment $fragment, ?Template $template): array {
        if( !$template ) {
            throw new ValueError(sprintf('Template "%s" not found', $fragment->getTemplateName()));
        }
        // Error : Blocking, the fragment can not work at all
        // Warning : Non-blocking, the fragment could work, or it could behave unpredictably
        $errors = $warnings = [];
        $errorSet = [&$errors, &$warnings];

        $fragmentProperties = $fragment->getProperties();
        $templateProperties = $template->getProperties();
        // Loop on template Properties
        foreach( $templateProperties as $propertyName => $propertySignature ) {
            $propertyValue = $fragmentProperties[$propertyName] ?? null;
            $this->getRecursivePropertyErrors($errorSet, $propertyName, $propertySignature, $propertyValue, '');
            // Remove for check about property in fragment not in template
            unset($fragmentProperties[$propertyName]);
        }
        unset($propertyName, $propertySignature, $propertyValue, $templateProperties);

        // Loop on fragment properties left
        foreach( $fragmentProperties as $propertyName => $propertyValue ) {
            // The property is in fragment but no more in template
            if( $propertyName === '_related' ) {
                // _related is a virtual property, this is related entities that would be loaded in template
                // TODO Write documentation
                continue;
            }
            $warnings[] = sprintf('Orphan fragment property "%s", no more in template', $propertyName);
        }
        unset($propertyName, $propertyValue, $fragmentProperties);

        $fragmentChildren = $fragment->getChildren();
        $templateChildren = $template->getChildren();
        // Loop on template Children
        foreach( $templateChildren as $childName => $childSignature ) {
            /** @var Fragment|Fragment[] $childFragments */
            $childFragments = $fragmentChildren[$childName] ?? null;
            if( $childFragments === null ) {
                if( $childSignature['required'] ) {
                    // The child is required by template but missing in fragment
                    $errors[] = sprintf('Missing required child "%s"', $childName);
                }
            } else {
                // Check child integrity
                $isList = is_array($childFragments);
                if( $childSignature['multiple'] ) {
                    if( !$isList ) {
                        // The template is expecting a list but this is a unique item
                        $errors[] = sprintf('Invalid child "%s", expecting a list but got a unique item', $childName);
                    }
                } else {
                    if( $isList ) {
                        // The template is expecting a unique item but this is a list
                        $errors[] = sprintf('Invalid child "%s", expecting a unique item but got a list', $childName);
                    }
                }

                // Remove for check about child in fragment not in template
                unset($fragmentChildren[$childName]);
            }
        }
        unset($childName, $childSignature, $childFragments, $isList, $templateChildren);

        // Loop on fragment properties left
        foreach( $fragmentChildren as $childName => $childFragments ) {
            // The property is in fragment but no more in template
            $warnings[] = sprintf('Orphan fragment child "%s", no more in template', $childName);
        }
        unset($childName, $childFragments, $fragmentChildren);


        return $errorSet;
    }

    protected function getRecursivePropertyErrors(array &$errorSet, string $name, array $signature, mixed $value): void {
        [&$errors, &$warnings] = $errorSet;
        if( $value === null ) {
            if( $signature['required'] ) {
                // The property is required by template but missing in fragment
                $errors[] = sprintf('Missing required property "%s"', $name);
            }
            return;
        }
        switch( $signature['type'] ) {
            case FragmentService::PROPERTY_TYPE_STRING:
                if( !is_string($value) ) {
                    $errors[] = sprintf('Property "%s" is invalid in fragment, expecting a string and got [%s]', $name, gettype($value));
                }
                break;
            case FragmentService::PROPERTY_TYPE_RICH_TEXT:
                if( !is_array($value) ) {
                    $errors[] = sprintf('Property "%s" is invalid in fragment, expecting a rich text array and got [%s]', $name, gettype($value));
                } else {
                    if( !isset($value['format']) ) {
                        $errors[] = sprintf('Property "%s" is invalid in fragment, missing format', $name);
                    }
                    if( !isset($value['text']) ) {
                        $errors[] = sprintf('Property "%s" is invalid in fragment, missing text', $name);
                    }
                }
                break;
            case FragmentService::PROPERTY_TYPE_OBJECT:
                if( !is_array($value) ) {
                    $errors[] = sprintf('Property "%s" is invalid in fragment, expecting an map array and got [%s]', $name, gettype($value));
                } else {
                    foreach( $signature['properties'] as $propertyName => $propertySignature ) {
                        $propertyValue = $value[$propertyName] ?? null;
                        $this->getRecursivePropertyErrors($errorSet, $name . '.' . $propertyName, $propertySignature, $propertyValue);
                        unset($value[$propertyName]);
                    }
                    foreach( $value as $propertyName => $propertyValue ) {
                        $warnings[] = sprintf('Orphan fragment property "%s", no more in template', $name . '.' . $propertyName);
                    }
                }
                break;
            case FragmentService::PROPERTY_TYPE_LIST:
                if( !is_array($value) ) {
                    $errors[] = sprintf('Property "%s" is invalid in fragment, expecting an indexed array and got [%s]', $name, gettype($value));
                } else {
                    foreach( $value as $itemIndex => $itemValue ) {
                        $this->getRecursivePropertyErrors($errorSet, $name . '[' . $itemIndex . ']', $signature['items'], $itemValue);
                    }
                }
                break;
        }
    }

}
