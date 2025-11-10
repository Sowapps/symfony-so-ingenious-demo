import { AbstractController } from "../../../core/controller/abstract.controller.js";
import { Modal } from 'bootstrap';
import { dialogService } from "../../../service/web/dialog.service.js";

export default class DialogController extends AbstractController {

	static values = {initOpen: Boolean, closeToPrevious: {type: Boolean, default: true}};
	static actives = [];

	initialize() {
		this.modal = Modal.getOrCreateInstance(this.element);
		if( this.hasInitOpenValue && this.initOpenValue ) {
			this.open();
		}
		this.closeForNextDialog = false;
		this.on('hidden.bs.modal')
			.then(() => {
				console.log('Closing dialog - Modal is now hidden ', this, this.element, this.closeToPreviousValue, this.closeForNextDialog);
				if( !this.closeForNextDialog ) {
					// Remove this dialog from active ones
					dialogService.removeLast();
					if( this.closeToPreviousValue ) {
						// We open back the previous dialog when closing this one
						// But not when we are closing it because we are opening a new one
						dialogService.openLast();
					}
				}
			});
	}

	close(closeForNextDialog = false) {
		console.log('Close dialog', closeForNextDialog);
		if( typeof closeForNextDialog === 'object' ) {
			// Set default when passing Event object
			const event = closeForNextDialog;
			closeForNextDialog = !!(event.detail && event.detail.next);// Default is false
		}
		this.closeForNextDialog = closeForNextDialog;
		this.modal.hide();
	}

	open(event) {
		let data = null, prefix = null, pattern = null;
		if( event && event.detail ) {
			prefix = event.detail.prefix || 'item';
			pattern = event.detail.pattern;
			data = event.detail.data || event.detail;
		}
		// console.log('Open dialog with', data, 'prefix', prefix, 'and pattern', pattern);
		if( data ) {
			$(this.element).fill(prefix, data);
			if( pattern ) {
				$(this.element).fillByName(data, pattern);
			}
		}
		if( this.closeToPreviousValue ) {
			// We close the previous dialog as we are opening a new one
			dialogService.closeLast();
		}
		// Show dialog
		console.log('Showing dialog', this);
		dialogService.open(this);
		// this.modal.show();
		// // Save this dialog as current last dialog
		// DialogController.actives.push(this);
		// console.log('Adding DialogController.actives', [...dialogService.actives]);
		// dialogService.showActives();
	}
}

// Active dialogs in pile/LIFO order
// DialogController.closeAll();

