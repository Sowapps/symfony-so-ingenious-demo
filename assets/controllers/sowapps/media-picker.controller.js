import { AbstractController } from "../../core/controller/abstract.controller.js";

export class MediaPickerController extends AbstractController {

	initialize() {
		this.config = null;
		// console.log('Declare picker to dialog ', this);
		this.dispatchEvent(this.element, 'so.media-picker.register-picker', {controller: this}, {bubbles: true});
	}

	setConfig(config) {
		this.config = config;
	}

	getPurpose() {
		return this.config.purpose;
	}

	getSelectionMax() {
		return this.config.selectMax;
	}

	getKey() {
		return this.element.dataset.key;
	}

	getLabel() {
		return this.element.dataset.label;
	}

	start() {

	}

	end() {

	}

}
