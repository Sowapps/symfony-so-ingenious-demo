import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
	
	connect() {
		$(this.element).val(Intl.DateTimeFormat().resolvedOptions().timeZone).change();
	}
	
}
