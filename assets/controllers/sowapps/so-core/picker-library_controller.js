import { MediaPickerController } from "../media-picker.controller.js";

export default class MediaPickerLibraryController extends MediaPickerController {
	
	static targets = ['row', 'rowView', 'thumbnailView'];
	static values = {listUrl: String};
	
	static STATE_NONE = 'none';
	static STATE_LOADING = 'loading';
	static STATE_LOADED = 'loaded';
	
	initialize() {
		super.initialize();
		console.log('MediaPickerLibraryController.initialize', this.listUrlValue);
		this.views = {
			row: {name: 'row', list: this.rowViewTarget, controller: 'sowapps--so-core--list-file-row'},
			thumbnail: {name: 'thumbnail', list: this.thumbnailViewTarget, controller: 'sowapps--so-core--list-file-thumbnail'},
		};
		// FILE_ID => {file: fileJson, selected: bool}
		this.items = {};
		this.state = MediaPickerLibraryController.STATE_NONE;
		this.view = this.views.row;
	}
	
	start() {
		console.log('MediaPickerLibraryController.start', this.getPurpose());
		if( this.state === MediaPickerLibraryController.STATE_NONE ) {
			this.refreshItems();
		}
	}
	
	async refreshItems() {
		console.log('Library.refreshItems');
		if( this.state === MediaPickerLibraryController.STATE_LOADING ) {
			// Already loading
			return;
		}
		this.state = MediaPickerLibraryController.STATE_LOADING;
		
		const list = await fetch(this.listUrlValue)
			.then(response => {
				if( !response.ok ) {
					throw response.json();
				}
				return response.json();
			});
		// console.log('Fetched list', list);
		const currentItems = this.items;
		this.items = list.map(file => {
			if( currentItems[file.id] ) {
				// Re-use known items
				return currentItems[file.id];
			}
			return this.createFileItem(file);
		});
		// console.log('Items ', this.items);
		
		this.state = MediaPickerLibraryController.STATE_LOADED;
		
		this.refreshView();
	}
	
	createFileItem(file) {
		return {
			file: file,
			selected: false,
		};
		// const fileItem = {
		// 	data: file,
		// 	selected: false,
		// 	// view: {
		// 	// },
		// };
		// Object.entries(this.views).forEach(([name, view]) => {
		// 	const $template = domService.renderTemplate(view.itemTemplate, file);
		// 	console.log('$template', $template);
		// 	try {
		// 		$template.querySelector('.item-file').file = file;
		// 		fileItem.view[name] = $template;
		// 	} catch(error) {
		// 		// Invalid template
		// 		console.warn('Invalid file template with no item-file element with sowapps--so-core--item-file controller', error);
		// 	}
		// });
		// return fileItem;
	}
	
	getSortedItems() {
		return this.items;
	}
	
	/**
	 * @param view
	 * @returns {FileListController}
	 */
	getViewListController(view) {
		return this.getController(view.list, view.controller);
	}
	
	refreshView() {
		const items = this.getSortedItems();
		const view = this.view;
		const viewController = this.getViewListController(view);
		// Empty
		viewController.clear();
		// Fill
		items.forEach(item => {
			viewController.add(item);
			// view.list.append(item.view[view.name]);
		});
		viewController.render();
	}
	
	
}
