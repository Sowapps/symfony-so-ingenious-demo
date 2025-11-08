/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */
import { apiDiagnosticService } from "../api-diagnostic.service.js";

class ConsoleService {

    includeServices() {
        apiDiagnosticService.initialize();
    }

}

export const consoleService = new ConsoleService();
