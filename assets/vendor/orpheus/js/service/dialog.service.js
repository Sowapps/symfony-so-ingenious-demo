class DialogService {
	
	constructor() {
		this.actives = [];
	}
	
	open(dialog) {
		dialog.modal.show();
		this.actives.push(dialog);
		this.showActives('Open new dialog')
	}
	
	showActives(label = 'showActives') {
		console.log(label + ' - actives', this.actives.map(item => item.modal._element), [...this.actives]);
	}
	
	removeLast() {
		this.showActives("removeLast");
		console.log('removeLast - this.actives', [...this.actives]);
		// this.actives.pop();
		const removed = this.actives.pop();
		console.log('removeLast', removed ? removed.modal : removed);
	}
	
	closeLast() {
		console.log('closeLast - this.actives', [...this.actives]);
		const lastDialog = this.actives.at(-1);
		console.log('closeLast', lastDialog ? lastDialog.modal : lastDialog);
		if( lastDialog ) {
			// Back open previous dialog, no changes are expected
			lastDialog.close(true);
		}
	}
	
	openLast() {
		console.log('openLast - this.actives', [...this.actives]);
		const lastDialog = this.actives.pop();
		console.log('openLast - lastDialog', lastDialog ? lastDialog.modal : lastDialog);
		if( lastDialog ) {
			// Back open previous dialog, no changes are expected
			lastDialog.open();
		}
	}
	
	closeAll() {
		console.trace('closeAll');
		// this.actives = [];
	}
	
}

console.log('Initialize dialogService');
export const dialogService = new DialogService();
