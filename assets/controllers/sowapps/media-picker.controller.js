import { AbstractController } from "./abstract.controller.js";

export class MediaPickerController extends AbstractController {
	
	initialize() {
		console.log('MediaPickerController starting');
		this.config = null;
		this.dispatchEvent(this.element, 'so.media-picker.register-picker', {controller: this}, {bubbles: true});
	}
	
	setConfig(config) {
		this.config = config;
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
