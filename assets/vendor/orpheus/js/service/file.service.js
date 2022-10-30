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
	
	getFileImageClass(file) {
		const [type, format] = file.mimeType.split('/');
		// Font Awesome Icons
		if( type === 'image' ) {
			return 'fa-solid fa-file-image';
			
		} else if( format === 'csv' ) {
			return 'fa-solid fa-file-csv';
			
		} else if( type === 'text' ) {
			return 'fa-solid fa-file-lines';
			
		} else if( format === 'pdf' ) {
			return 'fa-solid fa-file-pdf';
			
		} else if( ['vnd.ms-excel', 'vnd.openxmlformats-officedocument.spreadsheetml.sheet'].includes(format) ) {
			return 'fa-solid fa-file-excel';
			
		} else if( ['msword', 'vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(format) ) {
			return 'fa-solid fa-file-word';
			
		} else if( ['vnd.ms-powerpoint', 'vnd.openxmlformats-officedocument.presentationml.presentation'].includes(format) ) {
			return 'fa-solid fa-file-powerpoint';
		} else {
			return 'fa-solid fa-file';
		}
	}
	
	getFileAsIcon(file) {
		// Many missing file type, see https://fontawesome.com/search?q=file&m=free&s=solid%2Cbrands
		// And also https://developer.mozilla.org/fr/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
		return `<i class="${this.getFileImageClass(file)} fa-fw"></i>`;
	}
	
	getFileAsImage(file) {
		const [type, format] = file.mimeType.split('/');
		if( type === 'image' ) {
			return `<img src="${file.viewUrl}" alt="${file.label}" class="img-thumbnail">`;
		}
		return `<i class="${this.getFileImageClass(file)} icon-image"></i>`;
	}
	
}

export const fileService = new FileService();
