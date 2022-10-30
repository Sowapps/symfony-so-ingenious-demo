<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Test;

use Sowapps\SoCore\Core\Form\AbstractUserForm;
use Sowapps\SoCore\Form\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class TestEntityForm extends AbstractUserForm {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		dump('TestEntityForm', $options['data'] ?? 'NONE', $builder->getData());
		$builder->add('avatar', FileType::class);
		//		$builder->add('avatar', EntityType::class, [
		//			'class' => File::class,
		//		]);
	}
	
	//	public function configureOptions(OptionsResolver $resolver): void {
	//		$resolver->setDefaults([
	//			'data_class' => User::class,
	//			//			'data_class' => TestEntity::class,
	//			//			'inherit_data' => true,
	//			//			'by_reference' => false,
	//		]);
	//	}
	
}
