import { Controller } from 'stimulus';

export default class extends Controller {
	
	connect() {
		$(this.element).val(Intl.DateTimeFormat().resolvedOptions().timeZone).change();
	}
	
}
