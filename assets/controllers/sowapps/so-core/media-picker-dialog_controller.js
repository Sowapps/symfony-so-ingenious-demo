import { AbstractController } from "../abstract.controller.js";
import { domService } from "../../../vendor/orpheus/js/service/dom.service.js";
import { stringService } from "../../../vendor/orpheus/js/service/string.service.js";
import { Tab } from 'bootstrap';
import MediaPickerLibraryController from "./picker-library_controller.js";

export default class extends AbstractController {
	
	static targets = ['menu', 'menuItemTemplate', 'confirm'];
	
	#pickers;
	#config;
	
	initialize() {
		// console.log('Component media-picker-dialog starting');
		this.#pickers = {};
		this.selectedFiles = [];
		this.#config = null;
		
		this.element.addEventListener('so.media-picker.register-picker', event => {
			// console.log("Caught event so.media-picker.register-picker", event);
			this.registerPicker(event.detail.controller);
		});
		
		this.element.addEventListener('so.media-picker.file.new', async event => {
			const data = event.detail;
			console.log("Caught event so.media-picker.file.new", data);
			const picker = this.#pickers.library;
			const refresh = picker.controller.state !== MediaPickerLibraryController.STATE_NONE;
			picker.tab.show();
			if( refresh ) {
				picker.controller.refreshItems();
			}
			picker.controller.on(MediaPickerLibraryController.EVENT_REFRESHED)
				.then(() => {
					// Run once
					picker.controller.off(MediaPickerLibraryController.EVENT_REFRESHED);
					picker.controller.select(data.file);
				});
		});
		
		this.on('so.media-picker.library.changed').then(selection => {
			console.log("so.media-picker.library.changed - selection", selection);
			this.selectedFiles = selection.items;
			this.render();
		});
		
		this.render();
	}
	
	render() {
		const hasSelection = !!this.selectedFiles.length;
		this.confirmTargets.forEach($confirm => {
			$confirm.disabled = !hasSelection;
		});
	}
	
	close() {
		// Close dialog
		this.dispatchEvent(this.element, 'app.dialog.close');
	}
	
	cancel() {
		this.close();
	}
	
	confirm() {
		// const selection = this.selectedFiles;
		console.log('confirm - selection', this.selectedFiles);
		if( this.#config.target ) {
			// Only handle one-item selection for now
			try {
				this.#config.target.setFile(this.selectedFiles.length ? this.selectedFiles[0] : null);
			} catch( error ) {
				console.error('An error occurred setting the file of input', error);
			}
		} else {
			console.warn('No target');
		}
		this.close();
	}
	
	/**
	 * @param {MediaPickerController} picker Picker controller inheriting MediaPickerController
	 */
	async registerPicker(picker) {
		// console.log('Register picker', picker.getKey(), picker);
		const pickerKey = picker.getKey();
		const panelId = 'MediaPicker' + stringService.capitalize(pickerKey);
		const pickerConfig = {controller: picker, panelId: panelId, body: picker.element};
		const $panel = picker.element.matches('.tab-pane') ? picker.element : picker.element.closest('.tab-pane');
		$panel.id = panelId;
		pickerConfig.menuItem = await domService.renderTemplate(this.menuItemTemplateTarget, {
			panelId: panelId,
			label: picker.getLabel(),
		});
		this.menuTarget.append(pickerConfig.menuItem);
		const $tabItem = pickerConfig.menuItem.querySelector('.nav-link');
		pickerConfig.tab = new Tab($tabItem);
		this.#pickers[pickerKey] = pickerConfig;
		$tabItem.addEventListener('show.bs.tab', () => pickerConfig.controller.start());
		$tabItem.addEventListener('hidden.bs.tab', () => pickerConfig.controller.end());
	}
	
	request(event) {
		const data = event.detail;
		const selectedFile = data.selected;
		delete data.selected;
		// console.log('Media Library Dialog - Request', this.element, event, data, 'selectedFile is', selectedFile);
		this.#config = data;
		// Fill dialog
		// const mediaPickers = ['upload', 'library'];
		// Open requested picker or library if any file selected or first one
		let openPicker = this.#config.picker || (selectedFile ? 'library' : null);
		// console.log('openPicker', openPicker);
		// Reset / Hide all
		this.element
			.querySelectorAll('.nav-item.item-media-picker')
			.forEach($item => $item.hidden = true);
		let foundOpenPicker = false;
		let firstPicker = null;
		console.log('this.#pickers', this.#pickers);
		// Show selected pickers
		Object.entries(this.#pickers).forEach(([pickerKey, pickerConfig]) => {
			// const pickerConfig = this.#pickers[pickerKey];
			// console.log('Configure pickers with pickerConfig', pickerKey, pickerConfig);
			if( !pickerConfig ) {
				console.warn(`Unknown media picker with key "${pickerKey}"`);
				return;
			}
			if( !firstPicker ) {
				firstPicker = pickerKey;
			}
			if( openPicker && pickerKey === openPicker ) {
				foundOpenPicker = true;
			}
			pickerConfig.menuItem.hidden = false;
			pickerConfig.controller.setConfig(this.#config);
		});
		// Open first if no requested tab or invalid
		if( !foundOpenPicker ) {
			if( !firstPicker ) {
				console.warn(`No picker found for dialog`);
				return;
			}
			openPicker = firstPicker;
		}
		// openPicker is filled and valid
		const picker = this.#pickers[openPicker];
		if( selectedFile ) {
			picker.controller.select(selectedFile);
			this.selectedFiles = [selectedFile];// Invalid format but we just require to fill it
		}
		this.#pickers[openPicker].tab.show();
		
		// Open dialog
		this.dispatchEvent(this.element, 'app.dialog.open');
		
		this.render();
	}
	
}
