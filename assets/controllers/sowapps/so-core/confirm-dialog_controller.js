import { AbstractController } from "../../../core/controller/abstract.controller.js";
import {domService} from "../../../service/dom.service.js";
import { dialogService } from "../../../service/web/dialog.service.js";

export default class ConfirmDialogController extends AbstractController {

	static targets = ['cancel', 'confirm'];

	static invoke(title, message, data) {
		const eventData = {
			title: title,
			message: message,
			// event: event,
			data: data,
		};
		// this.dispatchEvent(window, 'so.confirm.request', eventData);
		return new Promise((resolve, reject) => {
			eventData.resolve = resolve;
			eventData.reject = reject;
			domService.dispatchEvent(window, 'so.confirm.request', eventData);
		});
	}

	initialize() {
		// Cohabit with Dialog controller
		console.log('Confirm SoCore Dialog', this.element, this.confirmTarget);
		this.on('hide.bs.modal')
			.then(() => {
				// Unbind any submit listener
				this.off('submit');
			});
		this.confirmation = null;
	}

	request(event) {
		const data = event.detail;
		// const submit = !data.event;
		// Fill dialog
		this.element.querySelectorAll('.modal-title').forEach(element => element.innerHTML = data.title);
		this.element.querySelectorAll('.dialog-legend').forEach(element => element.innerHTML = data.message);
		// Save data
		data.resolve = data.resolve || null;
		data.reject = data.reject || null;
		data.data = data.data || {};
		this.confirmation = data;
		if( data.submitName ) {
			// TODO Partially implemented
			this.confirmTargets.forEach(element => {
				element.setAttribute('name', data.submitName);
				element.setAttribute('value', data.submitValue);
			});
		}
		// Open dialog
		domService.dispatchEvent(this.element, 'app.dialog.open');
	}

	close(next) {
		// Close dialog
		domService.dispatchEvent(this.element, 'app.dialog.close', {next: next});
	}

	confirm(event) {
		if( event ) {
			event.preventDefault();
			event.stopPropagation();
		}
		console.log('ConfirmDialog - Confirm');
		dialogService.removeLast();// If getting back we don't want to confirm again
		this.close(true);// Removed so auto close won't work
		// TODO This closing will trigger open of previous
		domService.dispatchEvent(window, this.confirmation.event, this.confirmation.data);
		if( this.confirmation.resolve ) {
			this.confirmation.resolve();
		}
	}

	cancel(event) {
		if( event ) {
			event.preventDefault();
			event.stopPropagation();
		}
		console.log('ConfirmDialog - Confirm');
		this.close();
		if( this.confirmation.reject ) {
			this.confirmation.reject();
		}
	}

}
