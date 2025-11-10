import { AbstractController } from "../../../core/controller/abstract.controller.js";
import {domService} from "../../../service/dom.service.js";

export default class extends AbstractController {

	static targets = ['label', 'input', 'preview'];
	static values = {purpose: String, file: Object, emptyText: String, emptyUrl: String};

	initialize() {
		console.log('Component input-file starting', this.purposeValue);
		this.file = null;
		// Check input value
		console.log('file', this.hasFileValue, this.hasFileValue ? this.fileValue : 'No file value');
		if( this.hasFileValue ) {
			this.file = this.fileValue;
		}
		this.render();
	}

	connect() {
		// TODO Remove test
		// setTimeout(() => this.openDialog('upload'), 500);
		// setTimeout(() => this.openDialog('library'), 500);
	}

	getMax() {
		return this.element.dataset.max || 1;
	}

	setFile(file) {
		console.log('Set input file to file', file);
		this.file = file;
		this.render();
	}

	render() {
		const hasValue = !!this.file;
		const label = hasValue ? this.file.label : (this.hasEmptyTextValue ? this.emptyTextValue : '');
		this.inputTarget.value = hasValue ? this.file.id : '';
		if( this.hasLabelTarget ) {
			this.labelTarget.value = label;
		}
		if( this.hasPreviewTarget ) {
			this.previewTarget.src = hasValue && this.file.viewUrl ? this.file.viewUrl : this.emptyUrlValue;
			this.previewTarget.alt = label;
			this.previewTarget.title = label;
		}
		domService.toggleClass(this.element, 'has-value', hasValue);
		domService.toggleClass(this.element, 'has-no-value', !hasValue);
	}

	pick(picker) {
		console.log('Component input-file pick', picker);
		const data = {purpose: this.purposeValue, selectMax: this.getMax(), target: this, selected: this.file};
		if( typeof picker === 'string' ) {
			data.picker = picker;
		}
		this.dispatchEvent(window, 'so.media-library.request', data);
	}

}
