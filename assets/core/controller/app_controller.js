/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

import { AbstractMainController } from "./controllers.js";
import * as bootstrap from "../../vendor/bootstrap/bootstrap.index.js";
import { localStorageService } from "../../services/web/local-storage.service.js";

/**
 * @property {Boolean} hasExperimentalModalTarget
 * @property {Element} experimentalModalTarget
 */
export class AbstractAppMainController extends AbstractMainController {
	static targets = super.targets.concat(["experimentalModal"]);
	
	experimentalModal;
	
	async connect() {
		console.log("App main connect");
		if( this.hasExperimentalModalTarget ) {
			this.experimentalModal = new bootstrap.Modal(this.experimentalModalTarget);
			const experimentalWarningShowModal = localStorageService.get("experimentalWarning.showModal", true);
			if( experimentalWarningShowModal ) {
				this.experimentalModal.show();
			}
		}
		
		await super.connect();
	}
	
	closePermanentlyExperimentalModal() {
		console.log("ExperimentalModal close");
		this.experimentalModal.hide();
		localStorageService.set("experimentalWarning.showModal", false);
	}
	
}
