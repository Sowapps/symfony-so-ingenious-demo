import { AbstractController } from "../abstract.controller.js";
import { domService } from "../../../vendor/orpheus/js/service/dom.service.js";

export default class AlertDialogController extends AbstractController {
	
	static TYPE_ERROR = "error";
	static targets = ['confirm'];
	
	static error(title, message, data) {
		if( !message ) {
			message = title;
			title = null;
		}
		if( !title ) {
			title = translation.translate("so.error.title");
		}
		this.invoke(this.TYPE_ERROR, title, message, data);
	}
	
	static invoke(type, title, message, data) {
		const eventData = {
			type: type,
			title: title,
			message: message,
			data: data,
		};
		return new Promise((resolve, reject) => {
			eventData.resolve = resolve;
			eventData.reject = reject;
			domService.dispatchEvent(window, 'so.alert.request', eventData);
		});
	}
	
	initialize() {
		// Cohabit with Dialog controller
		console.log('SoCore ALERT Dialog', this.element, this.confirmTarget);
		this.on('hide.bs.modal')
			.then(() => {
				domService.dispatchEvent(window, this.confirmation.event, this.confirmation.data);
				if( this.confirmation.resolve ) {
					this.confirmation.resolve();
				}
			});
		this.confirmation = null;
		this.components = this.extractComponents({body: '.modal-body'});
		this.componentClasses = Object.fromEntries([
			[AlertDialogController.TYPE_ERROR, {
				body: "text-center text-danger fw-bold"
			}],
		]);
		console.log('this.components', this.components);
	}
	
	request(event) {
		const data = event.detail;
		// Apply style
		let componentClasses = this.componentClasses[data.type] || {};
		Object.entries(componentClasses).forEach(([key, className]) => {
			this.components[key].forEach(({element: element, className: initialClassName}) => element.className = initialClassName + " " + className);
		})
		// Fill dialog
		this.element.querySelectorAll('.modal-title').forEach(element => element.innerHTML = data.title);
		this.element.querySelectorAll('.dialog-legend').forEach(element => element.innerHTML = data.message);
		// Save data
		data.resolve = data.resolve || null;
		data.reject = data.reject || null;
		data.data = data.data || {};
		this.confirmation = data;
		// Open dialog
		domService.dispatchEvent(this.element, 'app.dialog.open');
	}
	
	close() {
		// Close dialog
		domService.dispatchEvent(this.element, 'app.dialog.close');
	}
	
	confirm(event) {
		if( event ) {
			event.preventDefault();
			event.stopPropagation();
		}
		console.log('ConfirmDialog - Confirm');
		this.close();
	}
	
}
