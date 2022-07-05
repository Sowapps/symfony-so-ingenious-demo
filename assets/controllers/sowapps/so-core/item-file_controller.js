import { AbstractController } from "../abstract.controller.js";
import { domService } from "../../../vendor/orpheus/js/service/dom.service.js";

console.log('FileItem included');
export default class extends AbstractController {
	
	initialize() {
		console.log('FileItem initialize', this.element.item);
		this.item = this.element.item;
		this.selectable = !!this.element.dataset.selectable;
		this.selected = this.item.selected;
		this.render();
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
	}
	
	// refreshSelect(event) {
	// 	console.log('item-file refreshSelect', event);
	// 	this.setSelected(event.target.checked);
	// }
	
	toggleSelect() {
		console.log('item-file - toggleSelect', this.selected, this.element);
		this.setSelected(!this.selected);
	}
	
	setSelected(value) {
		if( this.selected === value ) {
			// Ignore same value
			return;
		}
		this.selected = value;
		this.item.selected = value;
		this.dispatchEvent(this.element, 'so.file.select', {item: this.item, element: this.element}, {bubbles: true});
		this.render();
		
		return this;
	}
	
	render() {
		console.log('FileItem - render', this.element, 'this.selectable ?', this.selectable, 'this.selecting ?', this.selecting, 'this.selected ?', this.selected);
		if( this.selectable ) {
			if( !this.selecting ) {
				this.selecting = true;
				this.on('click')
					.then((count, event) => {
						console.log('event', event);//
						this.toggleSelect();
					});
			}
		} else {
			this.off('click');
			this.selecting = null;
		}
		domService.toggleClass(this.element, 'selectable', this.selectable);
		domService.toggleClass(this.element, 'selected', this.selected);
	}
	
}
