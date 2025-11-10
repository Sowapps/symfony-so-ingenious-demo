<?php /** @noinspection RegExpRedundantEscape */

/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintValidationException extends AbstractValidationException {
	
	public function __construct(
		protected readonly ConstraintViolationListInterface $errors
	) {
		parent::__construct();
	}
	
	public function getErrors(): ConstraintViolationListInterface {
		return $this->errors;
	}
	
	public function jsonSerialize(): array {
		$errors = [];
		foreach( $this->getErrors() as $error ) {
			$parameters = [];
			foreach( $error->getParameters() as $key => $value ) {
				$key = preg_replace('#\{\{ ?(.+) ?\}\}#', '$1', $key);
				$parameters[$key] = $value;
			}
			$errors[] = [
				'path'       => $error->getPropertyPath(),
				'message'    => $error->getMessage(),
				'parameters' => $parameters,
				'constraint' => $error->getConstraint()::class,
			];
		}
		
		return [
			'errors' => $errors,
		];
	}
	
}
