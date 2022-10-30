import { MediaPickerController } from "../media-picker.controller.js";
import { domService } from "../../../vendor/orpheus/js/service/dom.service.js";

export default class MediaPickerLibraryController extends MediaPickerController {
	
	static targets = ['row', 'rowButton', 'rowView', 'thumbnailButton', 'thumbnailView'];
	static values = {listUrl: String};
	
	static STATE_NONE = 'none';
	static STATE_LOADING = 'loading';
	static STATE_LOADED = 'loaded';
	static EVENT_REFRESHED = 'so.media-picker.library.refreshed';
	static EVENT_CHANGED = 'so.media-picker.library.changed';
	
	initialize() {
		super.initialize();
		console.log('MediaPickerLibraryController.initialize', this.listUrlValue);
		this.views = {
			row: {name: 'row', list: this.rowViewTarget, controller: 'sowapps--so-core--list-file-row', buttons: this.rowButtonTargets},
			thumbnail: {name: 'thumbnail', list: this.thumbnailViewTarget, controller: 'sowapps--so-core--list-file-thumbnail', buttons: this.thumbnailButtonTargets},
		};
		// FILE_ID => {file: fileJson, selected: bool}
		this.items = {};
		this.state = MediaPickerLibraryController.STATE_NONE;
		this.view = this.views.row;
		this.pendingSelection = [];
		
		this.on('so.list.selection.change')
			.then(selection => {
				console.log('Library - selection changes', selection);
				// The item list is the same, item are already marked as selected
				// But we are notified it happened
				
				// Format for library dialog output
				domService.dispatchEvent(this.element, 'so.media-picker.library.changed', {picker: this, items: [...selection.items]}, {bubbles: true});
			});
	}
	
	start() {
		// Start on show
		console.log('MediaPickerLibraryController.start', this.getPurpose(), this.state);
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
		
		const fileList = await fetch(this.listUrlValue)
			.then(response => {
				if( !response.ok ) {
					throw response.json();
				}
				return response.json();
			});
		console.log('Fetched list', fileList);
		const currentItems = this.items;
		this.items = Object.fromEntries(fileList.map(file => {
			let item = currentItems[file.id];
			if( !item ) {
				item = this.createFileItem(file);
			}// Else re-use known items
			return [file.id, item];
		}));
		console.log('Items ', this.items, 'dispatchEvent EVENT_REFRESHED');
		
		this.state = MediaPickerLibraryController.STATE_LOADED;
		
		if( this.pendingSelection.length ) {
			this.setSelection(this.pendingSelection);
		}
		
		this.dispatchEvent(this.element, MediaPickerLibraryController.EVENT_REFRESHED);
		
		this.refreshView();
	}
	
	changeView(name) {
		if( name instanceof Event ) {
			name = name.params.view;
		}
		if( !this.views[name] ) {
			throw new Error(`Unknown view with name ${name}, known views are [${Object.keys(this.views).join(', ')}]`);
		}
		if( this.view && this.view.name === name ) {
			// Ignore same view
			return;
		}
		this.view = this.views[name];
		this.refreshView();
	}
	
	select(file) {
		this.setSelection([file.id]);
	}
	
	setSelection(fileIds) {
		console.log('setSelection - fileIds', fileIds, Object.keys(this.items));
		if( this.state !== MediaPickerLibraryController.STATE_LOADED ) {
			// No loaded yet - we wait to get it loaded
			this.pendingSelection = fileIds;
			return;
		}
		this.pendingSelection = [];
		Object.values(this.items).forEach((fileItem) => {
			const fileIndex = fileIds.indexOf(fileItem.file.id);
			const selected = fileIndex >= 0;
			if( selected ) {
				fileIds = fileIds.splice(fileIndex, 1);
			}
			fileItem.selected = selected;
		});
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
		return Object.values(this.items);
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
		const otherViews = Object.values(this.views).filter(loopView => view !== loopView);
		console.log('refreshView - viewController', viewController);
		// Update views buttons
		otherViews.forEach(loopView => loopView.buttons.forEach($button => $button.classList.remove('active')));
		view.buttons.forEach($button => $button.classList.add('active'));
		// Update views visibility
		otherViews.forEach(loopView => loopView.list.hidden = true);
		view.list.hidden = false;
		// Empty
		viewController.clear();
		viewController.setSelectionMax(this.getSelectionMax());
		// Fill
		items.forEach(item => {
			viewController.add(item);
			// view.list.append(item.view[view.name]);
		});
		viewController.render();
	}
	
	
}
