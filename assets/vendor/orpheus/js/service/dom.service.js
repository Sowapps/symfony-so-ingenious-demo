import { isArray, isDefined, isDomElement, isObject } from "../orpheus.js";

class DomService {
	
	filters = {
		'upper': value => {
			return value.toUpperCase();
		},
		'date': (value, format) => {
			value = moment(value);
			return value.format(format);
		},
	}
	
	assignValue($element, value) {
		const elementTag = $element.tagName;
		let changed = false;
		if( elementTag === 'img' || elementTag === 'iframe' ) {
			$element.setAttribute('src', value);
		} else if( elementTag === 'a' ) {
			$element.setAttribute('href', value);
		} else if( this.isCheckbox($element) ) {
			if( $element.value.toLowerCase() !== 'on' ) {
				// Not default browser value
				$element.checked = isArray(value) ? value.includes($element.value) : $element.value === value;
			} else {
				// + to convert to int, !! to convert to boolean
				$element.checked = !!+value;
			}
			changed = true;
		} else if( elementTag === 'select' && $element.multiple && isArray(value) ) {
			// Create all missing options
			for( const subValue of value ) {
				let $option = $element.querySelector('option[value="' + subValue + '"]');
				if( !$option ) {
					// Automatically create new options
					$option = this.createElement('option');
					$option.innerText = subValue;
					$option.value = subValue;
					$element.append($option);
				}
			}
			// Set selected property for all options (to remove previous selected)
			$element.querySelectorAll('option').forEach($option => {
				$option.selected = value.includes($option.value);
			})
			changed = true;
		} else if( this.isInput($element) ) {
			// Fix issue in some dynamic forms
			// input was filled but the change event not called
			// dispatchEvent adds compatibility with Stimulus (raw event listener)
			$element.value = value;
			changed = true;
		} else {
			// Simple html element
			$element.innerText = value || $element.dataset.emptyText;
		}
		
		if( changed ) {
			$element.dispatchEvent(new Event('change'));
		}
		
	}
	
	isCheckbox($element) {
		return $element.tagName.toLowerCase() === 'input' && $element.getAttribute('type') === 'checkbox';
	}
	
	isInput($element) {
		return ['input', 'select', 'textarea'].includes($element.tagName.toLowerCase());
	}
	
	getInputs($element) {
		return $element.querySelectorAll('input,select,textarea');
	}
	
	getViewportSize() {
		// https://stackoverflow.com/questions/1248081/how-to-get-the-browser-viewport-dimensions
		return {
			width: Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0),
			height: Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0),
		};
	}
	
	addJsLibrary(url, options = {}) {
		const deferred = $.Deferred();
		
		const script = document.createElement('script');
		
		script.addEventListener('load', () => {
			deferred.resolve();
		});
		script.addEventListener('error', () => {
			deferred.reject('Failed to load script');
		});
		
		// load the script file
		script.src = url;
		document.body.appendChild(script);
		
		return deferred.promise();
	}
	
	resolveCondition(condition, data) {
		const conditionParts = condition.split(' ');
		var invert = conditionParts[0] === 'not';
		var property = conditionParts.length > 1 ? conditionParts[1] : conditionParts[0];
		return invert ^ Number(!!data[property]);
	}
	
	renderTemplateString(string, data) {
		// Clean contents
		let template = string.replace(/>\s+</ig, '><');
		
		// Resolve values in attributes
		// Deprecated for TWIG compatibility, double brackets
		template = template.replace(/\{\{ ([^\}]+) \}\}/ig, (all, variable) => {
			return this.resolveValue(variable, data);
		});
		// Yes we want this one, simple brackets
		template = template.replace(/\{ ?([^\}]+) ?\}/ig, (all, variable) => {
			return this.resolveValue(variable, data);
		});
		// For url compatibility, url encoded brackets
		template = template.replace(/\%7B\%20([^\%]+)\%20\%7D/ig, (all, variable) => {
			return this.resolveValue(variable, data);
		});
		
		return template;
	}
	
	resolveValue(value, data) {
		// First identify the value
		const firstChar = value.charAt(0);
		if( firstChar === '"' || firstChar === "'" ) {
			// Raw string
			value = value.slice(1, value.length - 1);
			return value;
		}
		let variable = value;
		let tokens = variable.split('|');
		// Calculate value
		const propertyTree = tokens.shift().trim().split('.');
		value = data;
		for( const property of propertyTree ) {
			value = value[property];
			if( value === undefined || value === null ) {
				// Stop now
				break;
			}
		}
		// Apply filters on value
		tokens.forEach(filterCall => {
			filterCall = filterCall.trim();
			// noinspection RegExpRedundantEscape
			const filterCallMatch = filterCall.match(/^([^\(]+)(?:\(([^\)]*)\))?$/);
			const filter = filterCallMatch[1];
			let filterArgs = [];
			if( filterCallMatch[2] ) {
				filterArgs = filterCallMatch[2].split(/,\s?/).map(argValue => this.resolveValue(argValue, data));
			}
			// Add value as first argument
			filterArgs.unshift(value);
			
			const filterCallback = this.filters[filter];
			if( filterCallback ) {
				value = filterCallback(...filterArgs);
			} else {
				console.warn('Unknown filter ' + filter);
			}
		});
		return value;
	}
	
	renderTemplateElement($template, data, prefix) {
		// Resolve conditional displays
		$template.querySelectorAll('[data-if]').forEach($element => {
			if( !this.resolveCondition($element.dataset.if, data) ) {
				$element.remove();
			}
		});
		// Fix image loading preventing
		$template.querySelectorAll('[data-src]').forEach($element => {
			$element.setAttribute('src', $element.dataset.src);
			$element.removeAttribute('data-src');
		});
		// Fix link crawling
		$template.querySelectorAll('[data-href]').forEach($element => {
			$element.attr('href', $element.dataset.href);
			$element.removeAttribute('data-href');
		});
		// Resolve values in content
		if( prefix ) {
			$template.fill(prefix, data);
		}
	}
	
	// TODO Convert to native
	// loadTemplate(key, target) {
	// 	const deferred = $.Deferred();
	// 	const $target = $(target);
	// 	$target.load('/api/template/' + key, (responseText, textStatus, jqXHR) => {
	// 		if( textStatus === 'success' ) {
	// 			deferred.resolve($target);
	// 		} else {
	// 			deferred.reject($target);
	// 		}
	// 	});
	// 	return deferred.promise();
	// }
	
	renderTemplate(template, data, options, wrapBc) {
		let isHtml = true;
		if( !template ) {
			throw new Error('Empty template');
		}
		// if( isDomElement(template) ) {
		// 	template = $(template);
		// }
		if( !isObject(options) ) {
			options = {prefix: options};
		}
		if( isDefined(wrapBc) ) {
			options.wrap = wrapBc;
		}
		options = Object.assign({
			prefix: null,
			wrap: false,
			immediate: false,// Some lib does not handle async
		}, options);
		if( isDomElement(template) ) {
			if( template.matches('template') ) {
				let content = template.innerHTML.trim();
				if( !content ) {
					content = template.innerText.trim();
					if( content ) {
						isHtml = false;
					}
				}
				// TODO Implement native remote
				// if( !content && template.dataset.key ) {
				// 	return this.loadTemplate(template.dataset.key, template)
				// 		.then(() => {
				// 			return this.renderTemplate(template, data, options.prefix);
				// 		});
				// }
				if( !content ) {
					console.error('Template has no contents', template);
				}
				template = content;
			} else {
				template = template.outerHTML;
			}
		}
		let renderedTemplate = this.renderTemplateString(template, data);
		// Create jquery object preventing jquery to preload images
		renderedTemplate = renderedTemplate.replace('\\ssrc=', ' data-src=');
		// If using text, we need to create a text node to use jQuery
		let $item = isHtml ? this.castElement(renderedTemplate) : document.createTextNode(renderedTemplate);
		if( options.wrap ) {
			const $wrapper = this.createElement('div', 'template-contents');
			$wrapper.append($item);
			$item = $wrapper;
		}
		// Direct in DOM Element
		$item.renderingTemplate = {template: template, options: options};
		this.renderTemplateElement($item, data, options.prefix);
		// console.log('$item', $item);
		// return options.immediate ? $item : $.when($item);
		return $item;
	}
	
	toggle(element, show) {
		element.style.display = show ? null : 'none';
	}
	
	showElement($element) {
		$element.show();
	}
	
	// buildAlert(message, type) {
	// 	return $('<div class="alert" role="alert"></div>').addClass('alert-' + type).text(message);
	// }
	//
	// buildErrorAlert(message) {
	// 	return this.buildAlert(message, 'danger');
	// }
	
	// show(selector, object, hideSelector, prefix) {
	// 	$(selector).each((index, element) => {
	// 		let $element = $(element);
	// 		if( $element.is('template') ) {
	// 			this.renderTemplate($element, object, prefix)
	// 				.then(($clone) => {
	// 					$clone.addClass($element.prop('class'));
	// 					$clone.data('controller', $element.data('controller'));
	// 					$element.after($clone);
	// 					this.showElement($clone);
	// 				});
	// 		} else {
	// 			this.showElement($element);
	// 		}
	// 	});
	// 	$(hideSelector).each((index, element) => {
	// 		let $element = $(element);
	// 		if( $element.data('rendering_template') ) {
	// 			// Remove templates' clone
	// 			$element.remove();
	// 		} else {
	// 			// Only hide others
	// 			$element.hide();
	// 		}
	// 	});
	// }
	
	detach($element) {
		if( !$element.parentElement ) {
			return false;
		}
		$element.parentElement.removeChild($element);
		return true;
	}
	
	castElement(fragment) {
		return document.createRange().createContextualFragment(fragment).firstElementChild;
	}
	
	createElement(tag, className, attributes) {
		const $element = document.createElement(tag);
		if( className ) {
			$element.className = className;
		}
		if( attributes && typeof attributes === 'object' ) {
			Object.entries(attributes).forEach(([key, value]) => {
				$element.setAttribute(key, value);
			});
		}
		return $element;
	}
	
	/**
	 * @param {Element} $element
	 * @param {string|Array<string>} classList
	 * @param {boolean|null} toggle True to add, false to remove, null to invert
	 */
	toggleClass($element, classList, toggle) {
		if( typeof classList === 'string' ) {
			classList = classList.split(' ');
		}
		for( const cssClass of classList ) {
			$element.classList.toggle(cssClass, toggle);
		}
	}
	
}

export const domService = new DomService();
