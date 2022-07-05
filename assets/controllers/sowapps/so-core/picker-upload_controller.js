import { MediaPickerController } from "../media-picker.controller.js";
import { fileService } from "../../../vendor/orpheus/js/service/file.service.js";
import { stringService } from "../../../vendor/orpheus/js/service/string.service.js";

export default class MediaPickerUploadController extends MediaPickerController {
	
	static targets = ['dropZone', 'dropError', 'uploadZone'];
	static values = {messages: Object, uploadUrl: String};
	#file = null;
	#allowedTypes = ['image/png', 'image/jpeg'];
	// #allowedTypes = [];
	
	initialize() {
		super.initialize();
		// console.log('Upload picker init with targets', this.targets);
		
		this.input = document.createElement('input');
		this.input.type = 'file';
		this.input.addEventListener('change', () => {
			this.onNewFile(this.input.files[0]);
		});
	}
	
	start() {
		super.start();
		this.startDrop();
	}
	
	startDrop() {
		this.uploadZoneTarget.hidden = true;
		this.dropZoneTarget.hidden = false;
	}
	
	startUpload() {
		this.dropZoneTarget.hidden = true;
		this.uploadZoneTarget.hidden = false;
	}
	
	async saveFile() {
		console.log('Save file', this.#file, 'to', this.uploadUrlValue);
		this.startUpload();
		const form = new FormData();
		form.append('file', this.#file);
		try {
			const file = await fetch(this.url(), {method: "POST", body: form})
				.then(response => {
					if( !response.ok ) {
						throw response.json();
					}
					return response.json();
				});
			console.log('Success upload, fetched ', file);
			this.dispatchEvent(this.element, 'so.media-picker.file.new', {controller: this, file: file}, {bubbles: true});
		} catch( error ) {
			console.warn('Upload error', error, typeof error);
			console.debug(error);
		} finally {
			this.startDrop();
		}
	}
	
	editFile() {
		console.log('Edit file', this.#file);
		// No editor here
		this.saveFile();
	}
	
	onNewFile(file) {
		if( !this.validateFile(file) ) {
			return;
		}
		this.#file = file;
		this.dropZoneTarget.hidden = true;
		this.clearDropError();
		this.editFile();
	}
	
	validateFile(file) {
		let error = fileService.checkFileType(file, this.#allowedTypes);
		if( error ) {
			this.addDropError(this.translate(error));
			return false;
		}
		return true;
	}
	
	clearDropError() {
		this.dropZoneTarget.classList.remove('state-error');
	}
	
	addDropError(text) {
		this.dropZoneTarget.classList.add('state-error');
		this.dropZoneTarget.querySelector('.error-message').innerText = text;
	}
	
	dropFile(event) {
		event.preventDefault();
		this.rejectFile();
		this.onNewFile(event.dataTransfer.files[0]);
	}
	
	rejectFile() {
		this.dropZoneTarget.classList.remove('state-error', 'dragging');
	}
	
	cancelDrop(event) {
		if( event.target !== this.dropZoneTarget ) {
			// Require children to have pointer-events: none;
			// Not leaving the drop zone, but a child
			return;
		}
		event.preventDefault();
		this.rejectFile();
	}
	
	acceptNewFile(event) {
		event.stopPropagation();
		event.preventDefault();
		let file = event.dataTransfer.files[0];
		if( !file && event.dataTransfer.items && event.dataTransfer.items[0] && event.dataTransfer.items[0].kind === 'file' ) {
			// While dragging files is unavailable but type is available trough item list
			file = event.dataTransfer.items[0];// Only type is available
		}
		// console.log('acceptNewFile', file, event, event.dataTransfer, event.dataTransfer.items[0], event.dataTransfer.items.length);
		if( file && !this.validateFile(file) ) {
			return false;
		}
		
		event.dataTransfer.dropEffect = 'link';
		this.dropZoneTarget.classList.add('dragging');
		this.clearDropError();
	}
	
	browse() {
		this.input.click();
	}
	
	url() {
		return stringService.replace(decodeURI(this.uploadUrlValue), {purpose: this.config.purpose});
	}
	
	translate(key) {
		return this.hasMessagesValue && this.messagesValue && this.messagesValue[key]? this.messagesValue[key] : key;
	}
	
}
