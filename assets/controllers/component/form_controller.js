import { AbstractController } from "../abstract.controller.js";

export default class Form extends AbstractController {
	
	static targets = ['submitButton'];
	static values = {delegate: Boolean, liveCheck: Boolean};
	
	initialize() {
		const $form = $(this.element);
		// Ensure we can find this element even this is not a <form>
		$form.addClass('controller-form');
		this.delegateValue = this.hasDelegateValue && this.delegateValue;
		this.liveCheckValue = this.hasLiveCheckValue && this.liveCheckValue;
		// Bootstrap 5 validation code
		// https://getbootstrap.com/docs/5.0/forms/validation/
		$form.on('submit', () => {
			// Unable to make it work
			// this.dispatchEvent(this.element, 'appformvalidating', {form: this.element});
			const valid = this.checkValidity();
			if( valid && this.delegateValue ) {
				$form.trigger('form.valid');
				return false;
			}
			
			$form.addClass('was-validated');
			if( valid ) {
				// Now submitting
				setTimeout(() => {
					// Wait form is submitting
					$form.addClass('state-submitting');
					$form.trigger('disable');
				}, 100);
			}
			$(this.element).find(':invalid').each((index, element) => {
				if( $(element).data('invalidate') ) {
					const $other = $($(element).data('invalidate'));
					$other[0].setCustomValidity('invalid');
				}
			});
			
			return valid;
		});
		// Deprecated ! Use this.disableSubmit()
		$form.on('disable', () => {
			console.debug('Disable form buttons');
			$form.getSubmitButtons().addClass('disabled').prop('disabled', true);
		});
		// Deprecated ! Use this.enableSubmit()
		$form.on('enable', () => {
			console.debug('Enable form buttons');
			$form.getSubmitButtons().removeClass('disabled').prop('disabled', false);
		});
		// const form = $form[0];
		$form[0].addEventListener('app.form.reset', () => this.reset());
		$form[0].addEventListener('app.form.disable-submit', () => this.disableSubmit());
		$form[0].addEventListener('app.form.enable-submit', () => this.enableSubmit());
		if( this.liveCheckValue ) {
			$form.getFields().on('change', () => {
				const valid = this.checkValidity();
				$form.getSubmitButtons().prop('disabled', !valid);
			});
		}
		// Call init change event
		// Exclude datetimepicker because change event is causing a wrong initialization of date controller
		$form.getFields().not('.datetimepicker-input').change();
	}
	
	enableSubmit() {
		this.submitButtonTargets.forEach(button => {
			button.disabled = false;
		});
	}
	
	disableSubmit() {
		this.submitButtonTargets.forEach(button => {
			button.disabled = true;
		});
	}
	
	reset() {
		if( this.element.nodeName === 'FORM' ) {
			this.element.reset();
		}
	}
	
	checkValidity() {
		$(this.element).find('.require-validation').trigger('app.form.validate');
		return this.element.checkValidity();
	}
}
