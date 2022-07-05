import { AbstractController } from "../abstract.controller.js";
import { domService } from "../../../vendor/orpheus/js/service/dom.service.js";
import { fileService } from "../../../vendor/orpheus/js/service/file.service.js";

export default class FileListController extends AbstractController {
	
	static targets = ['itemTemplate', 'list'];
	
	initialize() {
		console.log('FileListController.initialize TWO');
		this.$items = {};
		this.items = [];
		this.type = this.element.dataset.type;
		this.$list = this.hasListTarget ? this.listTarget : this.element;
		
		if( !this.type ) {
			throw new Error('Missing type (data-type) for FileListController');
		}
		
		// Permanent events
		this.on('so.file.select')
			.then(item => this.refreshSelectedItem(item));
	}
	
	getSelection() {
		return this.items.filter(item => item.selected);
	}
	
	refreshSelectedItem(item) {
		// Nothing for now
	}
	
	add(item) {
		console.log('list add item', item);
		this.items.push(item);
		// No auto refresh, you have to call render
		return this;
	}
	
	clear() {
		this.items = [];
		// No auto refresh, you have to call render
		return this;
	}
	
	render() {
		console.log('FileListController.render - this.itemTemplateTarget', this.itemTemplateTarget);
		// Clear list
		this.$list.innerHTML = '';
		// Build items
		this.items.forEach(item => this.$list.append(this.buildItem(item)));
	}
	
	buildItem(item) {
		console.log('buildItem', item, this.$items[item.file.id]);
		if( this.$items[item.file.id] ) {
			return this.$items[item.file.id];
		}
		// console.log('buildItem - build new');
		// const dataItem = item;
		const dataItem = {...item};// Shallow copy
		dataItem.icon = fileService.getFileAsIcon(item.file);
		const $item = domService.renderTemplate(this.itemTemplateTarget, dataItem);
		// console.log('$item', $item);
		try {
			const fileElement = $item.querySelector('.item-file');
			// console.dir(fileElement);
			fileElement.item = item;
		} catch( error ) {
			// Invalid template
			console.warn('Invalid file template with no item-file element with sowapps--so-core--item-file controller', error);
		}
		this.$items[item.file.id] = $item;
		// console.log('buildItem - built', $item);
		return $item;
	}
	
}
