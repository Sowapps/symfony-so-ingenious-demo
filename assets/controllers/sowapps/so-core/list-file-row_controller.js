import FileListController from "./list-file_controller.js";
import { domService } from "../../../vendor/orpheus/js/service/dom.service.js";
import { fileService } from "../../../vendor/orpheus/js/service/file.service.js";

export default class FileRowListController extends FileListController {
	
	static targets = super.targets.concat(['preview', 'previewNoneTemplate', 'previewOneTemplate', 'previewManyTemplate']);
	
	initialize() {
		super.initialize();
		console.log('FileRowListController.Init', this.previewTarget, this.listTarget);
	}
	
	refreshSelectedItem(item) {
		super.refreshSelectedItem(item);
		console.log('refreshSelectedItem - is row', item.element.parentNode, item.item.selected, item);
		this.render();
	}
	
	buildItem(item) {
		let $item = super.buildItem(item);
		if( item.selected ) {
			domService.toggleClass($item, 'active', item.selected);
		}
		return $item;
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
