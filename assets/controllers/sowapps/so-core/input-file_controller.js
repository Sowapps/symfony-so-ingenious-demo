import { AbstractController } from "../abstract.controller.js";

export default class extends AbstractController {
	
	static values = {purpose: String};
	
	initialize() {
		console.log('Component input-file starting', this.purposeValue);
	}
	
	connect() {
		setTimeout(() => this.openDialog(), 500);
	}
	
	openDialog() {
		console.log('Component input-file openDialog');
		this.dispatchEvent(window, 'so.media-library.request', {purpose: this.purposeValue, picker: 'library'});
	}
	
}
