import { AbstractController } from "../abstract.controller.js";
import { domService } from "../../../vendor/orpheus/js/service/dom.service.js";

export default class FileListController extends AbstractController {
	
	static targets = ['itemTemplate', 'list'];
	
	initialize() {
		this.$items = {};
		this.items = [];
		this.changedList = true;
		this.type = this.element.dataset.type;
		this.$list = this.hasListTarget ? this.listTarget : this.element;
		this.selectionMax = null;
		
		if( !this.type ) {
			throw new Error('Missing type (data-type) for FileListController');
		}
		
		// Permanent events
		this.on('so.file.select')
			.then(selection => {
				let changes;
				if( selection.value ) {
					changes = this.addToSelection(selection.item);
				} else {
					changes = this.removeFromSelection(selection.item);
				}
				if( changes ) {
					this.notifySelectionChanges();
				}
			});
		
		window.addEventListener('so.file.deleted', event => {
			console.log("Caught event so.file.deleted", event.detail.file);
			this.remove(event.detail.file);
			this.render();
		});
	}
	
	setSelectionMax(max) {
		this.selectionMax = max;
	}
	
	addToSelection(item) {
		console.log('addToSelection - item', item);
		if( item.selected ) {
			// Ignore already selected item
			return false;
		}
		// Check Max
		const selection = this.getSelection();
		if( selection.length >= this.selectionMax ) {
			// When only 1 as max, we switch, else we reject
			if( this.selectionMax === 1 ) {
				selection.forEach(item => item.selected = false);
			} else {
				return false;
			}
		}
		item.selected = true;
		return true;
	}
	
	removeFromSelection(item) {
		console.log('removeFromSelection - item', item);
		if( !item.selected ) {
			// Ignore already unselected item
			return false;
		}
		item.selected = false;
		return true;
	}
	
	notifySelectionChanges() {
		this.render();
		domService.dispatchEvent(this.element, 'so.list.selection.change', {list: this, items: this.getSelection().map(item => item.file)}, {bubbles: true});
	}
	
	getSelection() {
		return this.items
			.filter(item => item.selected);
	}
	
	remove(file) {
		let beforeCount = this.items.length;
		// Remove item by filtering it
		this.items = this.items.filter(item => item.file.id !== file.id);
		let afterCount = this.items.length;
		console.log('Removed file from', this.items, beforeCount, afterCount);
		// Remove item view
		delete this.$items[file.id];
		this.changedList = true;
	}
	
	add(item) {
		// console.log('list add item', item);
		if( !item.file ) {
			console.warn('Invalid item with no file property', item);
		} else {
			this.items.push(item);
			this.changedList = true;
		}
		// No auto refresh, you have to call render
		return this;
	}
	
	clear() {
		this.items = [];
		this.changedList = true;
		// No auto refresh, you have to call render
		return this;
	}
	
	render() {
		// console.log('FileListController.render - this.itemTemplateTarget', this.itemTemplateTarget);
		if( this.changedList ) {
			// Optimize rendering by only building items if list has changed (prevent selection to rebuild)
			// Clear list
			this.$list.innerHTML = '';
			// Build items
			this.items.forEach(item => this.$list.append(this.buildItem(item)));
			this.changedList = false;
		}
		// Then we refresh controller as this is now in DOM
		this.items
			.map(item => this.$items[item.file.id].querySelector('.item-file'))
			.forEach($item => this.dispatchEvent($item, 'so.item.render'));
	}
	
	buildItemData(item) {
	}
	
	buildItem(item) {
		console.log('buildItem', item, this.$items[item.file.id]);
		// Each list has its own file item
		if( this.$items[item.file.id] ) {
			return this.$items[item.file.id];
		}
		// console.log('buildItem - build new');
		// const dataItem = item;
		const dataItem = {...item};// Shallow copy
		this.buildItemData(dataItem);
		const $item = domService.renderTemplate(this.itemTemplateTarget, dataItem);
		console.log('FileList - buildItem $item', $item);
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
