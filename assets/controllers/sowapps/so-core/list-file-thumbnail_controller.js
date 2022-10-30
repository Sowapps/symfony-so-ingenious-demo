import FileListController from "./list-file_controller.js";
import { fileService } from "../../../vendor/orpheus/js/service/file.service.js";

export default class FileThumbnailListController extends FileListController {
	
	// static targets = super.targets.concat(['preview', 'previewNoneTemplate', 'previewOneTemplate', 'previewManyTemplate']);
	
	initialize() {
		super.initialize();
		console.log('FileThumbnailList.Init', this.previewTarget, this.listTarget);
	}
	
	buildItemData(item) {
		item.image = fileService.getFileAsImage(item.file);
	}
	
	// buildItem(item) {
	// 	let $item = super.buildItem(item);
	// 	domService.toggleClass($item, 'active', item.selected);
	// 	return $item;
	// }
	
	// render() {
	// 	console.log('FileThumbnailList - render');
	// 	super.render();
	// 	// Data
	// 	const selection = this.getSelection();
	// 	const count = selection.length;
	// }
	
}
