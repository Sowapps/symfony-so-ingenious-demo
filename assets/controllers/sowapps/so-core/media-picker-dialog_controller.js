import { AbstractController } from "../abstract.controller.js";
import { domService } from "../../../vendor/orpheus/js/service/dom.service.js";
import { stringService } from "../../../vendor/orpheus/js/service/string.service.js";
import { Tab } from 'bootstrap';

export default class extends AbstractController {
	
	static targets = ['menu', 'menuItemTemplate'];
	
	#pickers;
	#config;
	
	initialize() {
		// console.log('Component media-picker-dialog starting');
		this.#pickers = {};
		this.#config = null;
		
		this.element.addEventListener('so.media-picker.register-picker', event => {
			// console.log("Caught event so.media-picker.register-picker", event);
			this.registerPicker(event.detail.controller);
		});
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
		// console.log('pickerConfig', pickerConfig);
		this.#pickers[pickerKey] = pickerConfig;
		$tabItem.addEventListener('show.bs.tab', pickerConfig.controller.start());
		$tabItem.addEventListener('hidden.bs.tab', pickerConfig.controller.end());
	}
	
	request(event) {
		const data = event.detail;
		console.log('Media Library Dialog - Request', this.element, event, data);
		this.#config = data;
		// Fill dialog
		const mediaPickers = ['upload', 'library'];
		let openPicker = null;
		// Reset / Hide all
		this.element
			.querySelectorAll('.nav-item.item-media-picker')
			.forEach($item => $item.hidden = true);
		let foundOpenPicker = false;
		let firstPicker = null;
		// Show selected pickers
		mediaPickers.forEach(pickerKey => {
			const pickerConfig = this.#pickers[pickerKey];
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
		this.#pickers[openPicker].tab.show();
		
		// Open dialog
		this.dispatchEvent(this.element, 'app.dialog.open');
	}
	
}
