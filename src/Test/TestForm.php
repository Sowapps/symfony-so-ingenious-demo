<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Test;

use Sowapps\SoCore\Core\Form\AbstractForm;
use Sowapps\SoCore\Form\User\UserType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TestForm extends AbstractForm {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		dump('TestForm', $options, $builder->getData());
		$builder->add('user', UserType::class, [
			'models' => [UserType::MODEL_PICTURE => true],
		]);
		//		$builder->add('user', TestEntityForm::class, [
		//			'inherit_data' => false,
		//		]);
		$builder->add('save', SubmitType::class);
	}
	
}
