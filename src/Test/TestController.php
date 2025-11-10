<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Test;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractController {
	
	public function form(Request $request): Response {
		//		$test = new TestEntity();
		$test = $this->getUser();
		$form = $this->createForm(TestForm::class, ['user' => $test]);
		
		$form->handleRequest($request);
		if( $form->isSubmitted() && $form->isValid() ) {
			// $form->getData() holds the submitted values
			// but, the original `$task` variable has also been updated
			$data = $form->getData();
			dump($data);
		}
		
		return $this->render('test/test-form.html.twig', [
			'form' => $form,
		]);
	}
	
}
