import { AbstractController } from "../../../core/controller/abstract.controller.js";
import {domService} from "../../../service/dom.service.js";

export default class extends AbstractController {

	initialize() {
		// console.log('FileItem initialize', this.element.item);
		this.item = this.element.item;
		this.selectable = !!this.element.dataset.selectable;
		this.render();

		this.on('so.item.render')
			.then(() => this.render());
	}

	// open(event) {
	// 	event.preventDefault();
	// 	event.stopPropagation();
	// 	console.log('FileItem.open', event);
	// }

	remove(event) {
		event.preventDefault();
		event.stopPropagation();
		console.log('FileItem.remove', event);

		console.log('remove - fileItem', fileItem);
		this.fileManager.requestRemoveFile(fileItem.file);
	}

	// refreshSelect(event) {
	// 	console.log('item-file refreshSelect', event);
	// 	this.setSelected(event.target.checked);
	// }

	toggleSelect() {
		console.log('item-file - toggleSelect', this.isSelected(), this.element);
		this.setSelected(!this.isSelected());
	}

	isSelected() {
		return this.item.selected;
	}

	setSelected(value) {
		if( this.isSelected() === value ) {
			// Ignore same value
			return;
		}
		this.dispatchEvent(this.element, 'so.file.select', {item: this.item, element: this.element, value: value}, {bubbles: true});
		// this.item.selected = value;
		// this.render();

		return this;
	}

	render() {
		// console.log('FileItem - render', this.element, 'this.selectable ?', this.selectable, 'this.selecting ?', this.selecting, 'this.selected ?', this.isSelected());
		if( this.selectable ) {
			if( !this.selecting ) {
				this.selecting = true;
				this.on('click')
					.then((count, event) => {
						console.log('event', event);
						this.toggleSelect();
					});
			}
		} else {
			this.off('click');
			this.selecting = null;
		}
		// console.log('Render item', this.item, 'this.isSelected()', this.isSelected());
		domService.toggleClass(this.element, 'selectable', this.selectable);
		domService.toggleClass(this.element, 'selected', this.isSelected());
	}

}
