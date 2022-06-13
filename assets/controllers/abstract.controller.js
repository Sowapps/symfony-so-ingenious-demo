import { Controller } from 'stimulus';
import moment from "moment";
import { isJquery } from "../vendor/orpheus/js/orpheus.js";
import { Modal } from "bootstrap";

export class AbstractController extends Controller {
	
	dispatchEvent(element, event, detail = null) {
		if( element ) {
			if( element._element ) {
				// Auto handle BS Modals
				element = element._element;
			} else if( isJquery(element) ) {
				// Auto handle jQuery Elements
				element = element[0];
			}
		}
		element.dispatchEvent(new CustomEvent(event, detail ? {detail: detail} : null));
	}
	
	fixSelect2(element) {
		// Fix placeholder
		// Fix focus on search field
		$(element).on('select2:open', () => {
			$('.select2-container.select2-container--open .select2-search__field').prop('placeholder', $(element).data('searchPlaceholder'));
			document.querySelector('.select2-search__field').focus();
		})
	}
	
	/**
	 * @deprecated Use localeService.getLocale()
	 */
	getLocale() {
		return $('html').attr('lang');
	}
	
	checkImage(file, constraints) {
		constraints = Object.assign({}, {
			allowedTypes: null,
			minWidth: 0,
			maxWidth: Infinity,
			minHeight: 0,
			maxHeight: Infinity,
		}, constraints);
		const deferred = jQuery.Deferred();
		
		if( constraints.allowedTypes && !constraints.allowedTypes.includes(file.type) ) {
			deferred.reject(t('avatarEditor.invalidFileType'));
			
		} else {
			const image = new Image();
			
			image.onload = function () {
				// Check if image is bad/invalid
				if( this.width + this.height === 0 ) {
					this.onerror();
					return;
				}
				
				// Check the image resolution
				if(
					constraints.minWidth <= this.width && this.width <= constraints.maxWidth &&
					constraints.minHeight <= this.height && this.height <= constraints.maxHeight
				) {
					deferred.resolve(true);
				} else {
					deferred.reject(t('avatarEditor.invalidFileResolution'));
				}
			};
			
			image.onerror = function () {
				deferred.reject(t('avatarEditor.invalidFileType'));
			}
			
			image.src = URL.createObjectURL(file);
		}
		
		return deferred.promise();
	}
	
	parseMoment(date, value) {
		if( !value ) {
			value = date;
			date = moment();
		}
		const parsed = value.match(/@([\+\-]?)(\d+) ?([\w]+)/i);
		if( !parsed ) {
			return moment(value);
		}
		return parsed[1] === '-' ? date.subtract(parsed[2], parsed[3]) : date.add(parsed[2], parsed[3]);
	}
	
	createElementModal(name) {
		return this.createModal($(this.element).data(name));
	}
	
	createModal(selector) {
		return new Modal(document.querySelector(selector));
	}
	
}
