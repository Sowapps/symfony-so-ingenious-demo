import FileListController from "./list-file_controller.js";
import { domService } from "../../../vendor/orpheus/js/service/dom.service.js";
import { fileService } from "../../../vendor/orpheus/js/service/file.service.js";
import FileManager from "../../../service/file.manager.js";

export default class FileRowListController extends FileListController {
	
	static targets = super.targets.concat(['preview', 'previewNoneTemplate', 'previewOneTemplate', 'previewManyTemplate']);
	
	initialize() {
		super.initialize();
		this.fileManager = new FileManager();
		console.log('FileRowListController.Init', this.previewTarget, this.listTarget);
	}
	
	removeSelectedItem() {
		const selection = this.getSelection();
		if( !selection.length ) {
			console.warn('Tried to remove selection item but selection is empty');
			return;
		}
		const fileItem = selection[0];
		const file = fileItem.file;
		console.log('removeSelectedItem - fileItem', fileItem);
		this.fileManager.requestRemoveFile(file);
		// Manager is handling error by itself and success is emitted by event so.file.deleted
	}
	
	buildItemData(item) {
		item.icon = fileService.getFileAsIcon(item.file);
	}
	
	buildItem(item) {
		let $item = super.buildItem(item);
		// console.log('FileRowListController got item render event', item, $item);
		$item.querySelector('.item-file').addEventListener('so.item.render', () => this.refreshItem(item, $item));
		return $item;
	}
	
	// buildItem(item) {// Update using item event
	// 	let $item = super.buildItem(item);
	// 	this.refreshItem($item, item);
	// 	return $item;
	// }
	
	refreshItem(item, $item) {
		domService.toggleClass($item, 'active', item.selected);
	}
	
	render() {
		console.log('FileRowList - render');
		super.render();
		// Clear
		this.previewTarget.innerHTML = '';
		// Data
		const selection = this.getSelection();
		const count = selection.length;
		// Build new preview
		let $preview;
		if( count > 1 ) {
			// Many
			$preview = domService.renderTemplate(this.previewManyTemplateTarget, {count: count});
			
		} else if( count === 1 ) {
			// One
			const item = {...selection[0]};
			item.image = fileService.getFileAsImage(item.file);
			$preview = domService.renderTemplate(this.previewOneTemplateTarget, item);
			
		} else {
			// None
			$preview = domService.renderTemplate(this.previewNoneTemplateTarget);
		}
		this.previewTarget.append($preview);
	}
	
}
