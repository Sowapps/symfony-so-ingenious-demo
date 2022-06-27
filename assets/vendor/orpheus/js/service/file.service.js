/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

class FileService {
	
	
	checkFileType(file, types) {
		if( types && types.length && !types.includes(file.type) ) {
			return 'invalidFileType';
		}
		return null;
	}
	
}

export const fileService = new FileService();
