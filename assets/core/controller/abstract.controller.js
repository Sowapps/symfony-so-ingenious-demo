import { Controller } from '@hotwired/stimulus';
// import { isJquery } from "../vendor/orpheus/js/orpheus.js";
import { Modal } from "bootstrap";
import {Deferred} from "../event/Deferred.js";
import {domService} from "../../service/dom.service.js";
import {Is} from "../../helpers/is.helper.js";

export class AbstractController extends Controller {

	/**
	 * @param {Array} selectors
	 */
	extractComponents(selectors) {
		return Object.fromEntries(Object.entries(selectors).map(([key, selector]) =>
			([key, [...this.element.querySelectorAll(selector)].map(element => ({element: element, className: element.className}))])
		));
	}

	/**
	 * @deprecated Use eventService.dispatchEvent with same parameters
	 */
	dispatchEvent(element, event, detail = null, options = {}) {
		domService.dispatchEvent(element, event, detail, options)
	}

	#deferredEvent(type) {
		if( !this._events ) {
			this._events = {};
		}
		if( !this._events[type] ) {
			// console.log('New deferred event', type);
			this._events[type] = new Deferred(type);
			// } else {
			// 	console.log('Existing deferred event', type);
		}
		return this._events[type];
	}

	on(event) {
		const deferred = this.#deferredEvent(event);
		this.element.addEventListener(event, deferred.getListener());
		// console.log('Add event deferred', deferred.type, deferred.getListener());
		return deferred.promise();
	}

	/**
	 * @param {string|DeferredPromise} event
	 * @returns {Boolean}
	 */
	off(event) {
		const deferred = Is.string(event) ? this.#deferredEvent(event) : event.getRootDeferred();
		// console.log('Remove event deferred', deferred.type, deferred.getListener());
		this.element.removeEventListener(deferred.type, deferred.getListener());

		return true;
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

	createElementModal(name) {
		return this.createModal($(this.element).data(name));
	}

	createModal(selector) {
		return new Modal(document.querySelector(selector));
	}

	getController($element, name) {
		return this.application.getControllerForElementAndIdentifier($element, name);
	}

}
