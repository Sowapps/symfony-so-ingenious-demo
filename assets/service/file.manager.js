import { routingService } from "./web/routing.service.js";
import ConfirmDialogController from "../controllers/sowapps/so-core/confirm-dialog_controller.js";
import AlertDialogController from "../controllers/sowapps/so-core/alert-dialog_controller.js";
import {domService} from "./dom.service.js";


export default class FileManager {

	requestRemoveFile(file) {
		console.log('Confirm remove of file', file);

		return ConfirmDialogController
			.invoke(translation.translate('so.file.remove.confirm.title', file), translation.translate('so.file.remove.confirm.message', file), {file: file})
			.then(() => this.removeFile(file).catch(error => {
				// Error deleting file
				console.warn('Error deleting file', file, error);
				// TODO Open alert dialog to show error
				AlertDialogController.error(null, error.message);
				return error;
			}), () => {
				console.log('Rejected remove of file', file);
				throw 'cancelled';
			});
	}

	removeFile(file) {
		console.log('Execute remove of file', file);

		// new Request()
		const url = routingService.generate('so_core_api_file_delete', {id: file.id});
		console.log('url', url);

		return fetch(url, {
			method: 'DELETE',
		}).then(async response => {
			console.log('Delete response', response);
			if( !response.ok ) {
				throw await response.json();
			}
			console.log('Delete response is ok !', response.json());
			let data = {file: file};
			domService.dispatchEvent(window, 'so.file.deleted', data);

			return data;
		});
	}

}
