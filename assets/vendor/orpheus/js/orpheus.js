var _Translations = {};

function t(key, parameters) {
	let string = _Translations && _Translations[key] ? _Translations[key] : key;
	if( parameters ) {
		for( const [token, value] of Object.entries(parameters) ) {
			string = string.replace('{' + token + '}', value);
		}
	}
	return string;
}

export function provideTranslations(translations) {
	$.extend(_Translations, translations);
}

provideTranslations({
	'ok': "OK",
	'cancel': "Cancel",
});

function debug(t) {
	for( var i in arguments ) {
		console.log(arguments[i]);
	}
}

function clone(obj) {
	var target = {};
	for( var i in obj ) {
		if( obj.hasOwnProperty(i) ) {
			target[i] = obj[i];
		}
	}
	return target;
}

export function basename(string) {
	string = string.replace(/\\/g, '/');
	return string.substring(string.lastIndexOf('/') + 1);
}

export function nl2br(str, is_xhtml) {
	//	discuss at: http://phpjs.org/functions/nl2br/
	var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br/>' : '<br>';
	return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

export function formatDouble(n) {
	return ("" + n).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

export function isDefined(v) {
	return v !== undefined;
}

export function isSet(v) {
	return isDefined(v) && v !== null;
}

export function isScalar(obj) {
	return (/string|number|boolean/).test(typeof obj);
}

export function isString(v) {
	return typeof (v) === 'string';
}

export function isObject(v) {
	return v != null && typeof (v) === 'object';
}

export function isPureObject(v) {
	return isObject(v) && v.constructor === Object;
}

export function isArray(v) {
	return isObject(v) && v.constructor === Array;
}

export function isFunction(v) {
	return typeof (v) === 'function';
}

export function isDomElement(obj) {
	return isObject(obj) && obj instanceof HTMLElement;
}

export function isJquery(v) {
	return isObject(v) && typeof (v.jquery) !== 'undefined';
}

export function notJquery(v) {
	return isObject(v) && typeof (v.jquery) === 'undefined';
}

export function daysInMonth(year, month) {
	return new Date(year, month, 0).getDate();
}

export function str2date(val) {
	if( !val ) { /*debug(val);*/
		return null;
	}
	var d = val.split("/");
	if( !d || !d.length || d.length < 3 ) {
		return false;
	}
	return new Date(d[2], d[1] - 1, d[0]);
}

function leadZero(val) {
	val = val * 1;
	return val < 10 ? '0' + val : val;
}

var getLocation = function (uri) {
	var l = document.createElement("a");
	l.href = uri;
	return l;
};

function bintest(value, reference) {
	return checkFlag(value, reference);
}

function checkFlag(value, reference) {
	return ((value & reference) == reference);
}

export function number_format(number, decimals, dec_point, thousands_sep) {
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	const n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		toFixedFix = function (n, prec) {
			var k = Math.pow(10, prec);
			return '' + Math.floor(n * k) / k;
		};
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	let s = toFixedFix(n, prec).split('.');
	if( s[0].length > 3 ) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if( (s[1] || '').length < prec ) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}

var cache = {};

function requestAutocomplete(what, term, response) {
	if( cache[what] && term in cache[what] ) {
		response(cache[what][term]);
		return;
	}
	if( !cache[what] ) {
		cache[what] = {};
	}
	$.getJSON("remote-search-what=" + what + "&term=" + term + ".json", function (data, status, xhr) {
		if( data.code === "ok" ) {
			response(data.other);
			return;
		}
		response([]);
	});
}

String.prototype.capitalize = function () {
	if( typeof this !== "string" ) {
		return this;
	}
	return this.charAt(0).toUpperCase() + this.slice(1).toLowerCase();
};

String.prototype.upFirst = function () {
	return this.charAt(0).toUpperCase() + this.slice(1);
};

Date.prototype.getFullDay = function () {
	return "" + this.getFullYear() + leadZero(this.getMonth()) + leadZero(this.getDate());
};

var pageScrolled;

function PageScrollTo(sel, paddingTop) {
	if( pageScrolled ) {
		return;
	}
	sel = $(sel).first();
	if( !sel.length ) {
		return;
	}
	pageScrolled = 1;
	if( paddingTop === undefined ) {
		paddingTop = 10;
	}
	var offset = $(sel).offset();
	if( offset && offset.top ) {
		var to = offset.top - paddingTop;
		$("html").scrollTop(to);// Firefox
		$("body").animate({scrollTop: to}, 0);// Chrome, Safari
	}
}

if( $ ) {
	
	function flatData(data, pattern, target, childSuffix, ignoreArray) {
		if( !target ) {
			target = {};
		}
		if( !childSuffix ) {
			childSuffix = '[%s]';
		}
		for( var key in data ) {
			if( !data.hasOwnProperty(key) ) {
				continue;
			}
			var newKey = !pattern ? key : pattern.replace('%s', key);
			
			var value = data[key];
			if( isArray(value) ) {
				// Special case, the same field could have multiple values, so the value is an array
				if( !ignoreArray ) {
					// Default suffix only, ignore others
					target[newKey + '[]'] = value;
				}
			} else if( isPureObject(value) ) {
				flatData(value, newKey + childSuffix, target);
			} else {
				target[newKey] = value;
			}
		}
		return target
	}
	
	/**
	 * Fill all children of the current element with this data using the key and prefix to set the value
	 */
	$.fn.fill = function (prefix, data) {
		$(this).each(function () {
			var container = $(this);
			const flatten = flatData(data, prefix + '_%s', null, '_%s', true);
			$.each(flatten, function (key, value) {
				if( key.includes('[]') ) {
					return;
				}
				container.find("." + key).each(function () {
					$(this).assignValue(value);
				});
			});
		});
	};
	$.fn.fillByName = function (data, pattern = null) {
		$(this).each(function () {
			var container = $(this);
			$.each(flatData(data, pattern), function (key, value) {
				container.find(':input[name="' + key + '"]').each(function () {
					$(this).assignValue(value);
				});
			});
		});
	};
	
	$.fn.assignValue = function (value) {
		var $element = $(this);
		if( $element.is('img') || $element.is('iframe') ) {
			$element.attr('src', value);
		} else if( $element.is('a') ) {
			$element.attr('href', value);
		} else if( $element.is(':checkbox') ) {
			if( $element.val().toLowerCase() !== 'on' ) {
				// Not default browser value
				$element.prop('checked', isArray(value) ? value.includes($element.val()) : $element.val() === value);
			} else {
				// + to convert to int, !! to convert to boolean
				$element.prop('checked', !!+value);
			}
			$element.change();
		} else if( $element.is('select[multiple]') && isArray(value) ) {
			// Reset previous value
			$element.val('');
			// Assign all new values
			for( const subValue of value ) {
				let $option = $element.find('option[value="' + subValue + '"]');
				if( !$option.length ) {
					// Automatically create new options
					$option = $('<option></option>');
					$option.text(subValue);
					$option.val(subValue);
					$element.append($option);
				}
				// $option.prop('selected', true);
			}
			$element.val(value).change();
		} else if( $element.is(':input') ) {
			// Fix issue in some dynamic forms
			// input was filled but the change event not called
			// dispatchEvent adds compatibility with Stimulus (raw event listener)
			$element.val(value).change()[0].dispatchEvent(new Event('input', {bubbles: true}));
		} else {
			$element.text(value || $element.data('emptyText'));
		}
		
		return $element;
	}
	
	$.fn.contextualize = function (env) {
		$(this).each(function (key, value) {
			(function (container, env) {
				for( var key in env ) {
					if( env.hasOwnProperty(key) ) {
						this[key] = env[key];
					}
				}
				container.find("[data-showif]").each(function () {
					var showIf = $(this).data('showif');
					if( !showIf ) {
						return;
					}
					if( eval(showIf) ) {
						$(this).show();
					} else {
						$(this).hide();
					}
				});
				
			})($(this), env);
		});
	};
	
	/**
	 * Extract data from all children input using the data-field
	 */
	$.fn.extract = function () {
		var data = {};
		$(this).find(":input[data-field]").each(function () {
			data[$(this).data("field")] = $(this).val();
		});
		return data;
	};
	
	/**
	 * Get FormData from all children input by parsing their name
	 */
	$.fn.getFormData = function (silent) {
		var $form = $(this).getForm(silent);
		return new FormData($form.get(0));
	};
	
	/**
	 * Extract data object from all children input by parsing their name
	 */
	$.fn.getFormObject = function (silent) {
		var formData = $(this).getFormData(silent);
		var object = {};
		var buildObject = function (data, keys, value) {
			let key = keys.shift();
			if( !keys.length ) {
				if( !key ) {
					// Last empty returns value
					return value;
				}
				// Lone key with no brackets
				data[key] = value;
				return data;
			}
			value = buildObject((data && data[key]) || null, keys, value);
			if( data === null ) {
				if( !key ) {
					// Empty key in middle of string means []
					return [value];
				}
				return {[key]: value};
			}
			if( !key ) {
				// Empty key in middle of string means []
				data.push(value);
			} else {
				data[key] = value;
			}
			return data;
		};
		formData.forEach((value, name) => {
			object = buildObject(object, name.split(/[\[\]]{1,2}/), value);
		});
		return object;
	};
	
	$.fn.getForm = function (silent) {
		var $form = $(this).is("form") ? $(this) : $(this).closest("form");
		if( !$form.length ) {
			$form = $(this).find("form").first();
		}
		if( !$form.length ) {
			if( silent ) {
				return;
			}
			throw "Form not found";
		}
		return $form;
	};
	
	/**
	 * Reset a form
	 */
	$.fn.resetForm = function (silent) {
		$(this).each(function () {
			var form = $(this).is("form") ? $(this) : $(this).closest("form");
			if( !form.length ) {
				form = $(this).find("form").first();
			}
			if( !form.length ) {
				if( silent ) {
					return;
				}
				throw "Unable to reset form, form not found";
			}
			form.get(0).reset();
			// Don't reset hidden field to protect framework CSRF token
			form.find(':input:not(:button)').trigger('reset');
		});
	};
	
	$(":button[data-submit-text], :button[data-submittext]").each(function () {
		var button = $(this);
		var form = $(this).closest("form");
		var listener = function () {
			if( !button.data("submitted") ) {
				button.data("submitted", 1);
				button.data("submitOld", button.html());
				button.text(button.data("submitText") || button.data("submittext"));
			}
			if( !form.data("inputsDisabled") ) {
				form.data("inputsDisabled", 1);
				form.disableInputs();
			}
			$(form).one("cancelsubmit", function () {
				if( !button.data("submitted") ) {
					return;
				}
				button.html(button.data("submitOld"));
				form.data("inputsDisabled", 0);
				form.enableInputs();
			});
		};
		button.click(listener);
		form.submit(listener);
	});
	
	// $("input[data-preview]").change(function () {
	// 	var input = $(this);
	// 	var oFReader = new FileReader();
	// 	oFReader.readAsDataURL(this.files[0]);
	// 	oFReader.onload = function (oFREvent) {
	// 		$(input.data('preview')).attr('src', oFREvent.target.result);
	// 	};
	// });
}

// Source: http://stackoverflow.com/questions/3954438/remove-item-from-array-by-value
Array.prototype.remove = function () {
	var what, a = arguments, L = a.length, ax;
	while( L && this.length ) {
		what = a[--L];
		while( (ax = this.indexOf(what)) !== -1 ) {
			this.splice(ax, 1);
		}
	}
	return this;
};
if( !Array.prototype.indexOf ) {
	Array.prototype.indexOf = function (what, i) {
		i = i || 0;
		var L = this.length;
		while( i < L ) {
			if( this[i] === what ) return i;
			++i;
		}
		return -1;
	};
}

function centerInViewport(el) {
	el = $(el);
	debug(el);
	var viewportWidth = jQuery(window).width(),
		viewportHeight = jQuery(window).height(),
		elWidth = el.width(),
		elHeight = el.height(),
		elOffset = el.offset();
	jQuery(window)
		.scrollTop(elOffset.top + (elHeight / 2) - (viewportHeight / 2))
		.scrollLeft(elOffset.left + (elWidth / 2) - (viewportWidth / 2));
}

function moveOnMouse(el, e) {
	el = $(el);
	var elHeight = el.height(),
		elOffset = el.offset();
	var scrollTop = jQuery(window).scrollTop() - ((e.pageY - (elHeight / 2)) - elOffset.top);
	$(window).scrollTop(scrollTop);
}

if( typeof global.KeyEvent == "undefined" ) {
	global.KeyEvent = {
		DOM_VK_CANCEL: 3,
		DOM_VK_HELP: 6,
		DOM_VK_BACK_SPACE: 8,
		DOM_VK_TAB: 9,
		DOM_VK_CLEAR: 12,
		DOM_VK_RETURN: 13,
		DOM_VK_ENTER: 14,
		DOM_VK_SHIFT: 16,
		DOM_VK_CONTROL: 17,
		DOM_VK_ALT: 18,
		DOM_VK_PAUSE: 19,
		DOM_VK_CAPS_LOCK: 20,
		DOM_VK_ESCAPE: 27,
		DOM_VK_SPACE: 32,
		DOM_VK_PAGE_UP: 33,
		DOM_VK_PAGE_DOWN: 34,
		DOM_VK_END: 35,
		DOM_VK_HOME: 36,
		DOM_VK_LEFT: 37,
		DOM_VK_UP: 38,
		DOM_VK_RIGHT: 39,
		DOM_VK_DOWN: 40,
		DOM_VK_PRINTSCREEN: 44,
		DOM_VK_INSERT: 45,
		DOM_VK_DELETE: 46,
		DOM_VK_0: 48,
		DOM_VK_1: 49,
		DOM_VK_2: 50,
		DOM_VK_3: 51,
		DOM_VK_4: 52,
		DOM_VK_5: 53,
		DOM_VK_6: 54,
		DOM_VK_7: 55,
		DOM_VK_8: 56,
		DOM_VK_9: 57,
		DOM_VK_SEMICOLON: 59,
		DOM_VK_EQUALS: 61,
		DOM_VK_A: 65,
		DOM_VK_B: 66,
		DOM_VK_C: 67,
		DOM_VK_D: 68,
		DOM_VK_E: 69,
		DOM_VK_F: 70,
		DOM_VK_G: 71,
		DOM_VK_H: 72,
		DOM_VK_I: 73,
		DOM_VK_J: 74,
		DOM_VK_K: 75,
		DOM_VK_L: 76,
		DOM_VK_M: 77,
		DOM_VK_N: 78,
		DOM_VK_O: 79,
		DOM_VK_P: 80,
		DOM_VK_Q: 81,
		DOM_VK_R: 82,
		DOM_VK_S: 83,
		DOM_VK_T: 84,
		DOM_VK_U: 85,
		DOM_VK_V: 86,
		DOM_VK_W: 87,
		DOM_VK_X: 88,
		DOM_VK_Y: 89,
		DOM_VK_Z: 90,
		DOM_VK_CONTEXT_MENU: 93,
		DOM_VK_NUMPAD0: 96,
		DOM_VK_NUMPAD1: 97,
		DOM_VK_NUMPAD2: 98,
		DOM_VK_NUMPAD3: 99,
		DOM_VK_NUMPAD4: 100,
		DOM_VK_NUMPAD5: 101,
		DOM_VK_NUMPAD6: 102,
		DOM_VK_NUMPAD7: 103,
		DOM_VK_NUMPAD8: 104,
		DOM_VK_NUMPAD9: 105,
		DOM_VK_MULTIPLY: 106,
		DOM_VK_ADD: 107,
		DOM_VK_SEPARATOR: 108,
		DOM_VK_SUBTRACT: 109,
		DOM_VK_DECIMAL: 110,
		DOM_VK_DIVIDE: 111,
		DOM_VK_F1: 112,
		DOM_VK_F2: 113,
		DOM_VK_F3: 114,
		DOM_VK_F4: 115,
		DOM_VK_F5: 116,
		DOM_VK_F6: 117,
		DOM_VK_F7: 118,
		DOM_VK_F8: 119,
		DOM_VK_F9: 120,
		DOM_VK_F10: 121,
		DOM_VK_F11: 122,
		DOM_VK_F12: 123,
		DOM_VK_F13: 124,
		DOM_VK_F14: 125,
		DOM_VK_F15: 126,
		DOM_VK_F16: 127,
		DOM_VK_F17: 128,
		DOM_VK_F18: 129,
		DOM_VK_F19: 130,
		DOM_VK_F20: 131,
		DOM_VK_F21: 132,
		DOM_VK_F22: 133,
		DOM_VK_F23: 134,
		DOM_VK_F24: 135,
		DOM_VK_NUM_LOCK: 144,
		DOM_VK_SCROLL_LOCK: 145,
		DOM_VK_COMMA: 188,
		DOM_VK_PERIOD: 190,
		DOM_VK_SLASH: 191,
		DOM_VK_BACK_QUOTE: 192,
		DOM_VK_OPEN_BRACKET: 219,
		DOM_VK_BACK_SLASH: 220,
		DOM_VK_CLOSE_BRACKET: 221,
		DOM_VK_QUOTE: 222,
		DOM_VK_META: 224
	};
}
var Modifier = {
	CONTROL: 1,
	SHIFT: 2,
	ALT: 4,
	META: 8,
}

/* Orpheus Widget & JS Plugins */

var escapeHTML;
(function ($) {// Preserve our jQuery
	
	escapeHTML = function (str) {
		return $('<p></p>').text(str).html();
	}
	
	$.expr[':'].parents = function (a, i, m) {
		return $(a).parents(m[3]).length < 1;
	};
	
	$.fn.disableInputs = function () {
		return $(this).setFieldsReadonly().filter(":button").addClass("disabled");
	};
	
	$.fn.enableInputs = function () {
		return $(this).setFieldsWritable().filter(":button").removeClass("disabled");
	};
	
	$.fn.getFields = function () {
		return $(this).find(':input:not(button)');
	}
	
	$.fn.getSubmitButtons = function () {
		return $(this).find('button:submit,.btn-submit');
	}
	
	$.fn.setFieldsReadonly = function () {
		return $(this).find(':input').prop("readonly", true);
	};
	
	$.fn.setFieldsWritable = function () {
		return $(this).find(':input').prop("readonly", false);
	};
	
	$.fn.disableFields = function () {
		$(this).find(':input').prop("disabled", true);
		
		return $(this);
	};
	$.fn.enableFields = function () {
		return $(this).find(':input').prop("disabled", false);
	};
	
	/*
	 * This function run completable if cond is true and pass it complete to set the complete callback, but if cond is false, it just calls the complete callback immediatly
	 */
	$.fn.cond = function (cond, completable, complete) {
		this.completable = completable;
		this.complete = complete;
		if( cond && (cond === "boolean" || (cond instanceof jQuery && cond.length)) ) {
			return this.completable(this.complete);
		}
		return this.complete();
	};
	$.cond = $.fn.cond;
	
	$.fn.showIf = function (cond) {
		cond ? $(this).show() : $(this).hide();
		
		return this;
	};
	// Call when element is shown
	$.fn.shown = function (callback) {
		$(this).bind("shown", callback);
		if( $(this).is(":visible") ) {
			this.callback = callback;
			this.callback();
		}
	};
	// Scroll to element
	$.fn.scrollTo = function (option, event) {
		if( !option ) {
			option = "center";
		}
		var el = $(this).first();
		if( !el.length ) {
			return;
		}
		var viewportWidth = $(window).width(), viewportHeight = $(window).height(),
			elWidth = $(el).width(), elHeight = $(el).height(), elOffset = $(el).offset();
		if( option === "top" ) {
			$(window).scrollTop(elOffset.top + elHeight / 2 - 100);
		} else {
			// Default is center
			$(window).scrollTop(elOffset.top + elHeight / 2 - viewportHeight / 2);
		}
		$(window).scrollLeft(elOffset.left + elWidth / 2 - viewportWidth / 2);
	};
	
	// Apply on load and on change
	$.fn.watch = function (cb, sel) {
		sel ? $(this).on("change", sel, cb) : $(this).change(cb);
		$(this).each(function () {
			this.cb = cb;
			this.cb({currentTarget: this});
		});
	};
	
	$.fn.pressEnter = function (cb) {
		return $(this).keydown(function (e) {
			if( e.which === KeyEvent.DOM_VK_RETURN ) {
				e.preventDefault();
				this.callback = cb;
				return this.callback(e);
			}
		});
	};
	
	$.fn.pressKey = function (key, modifiers, cb) {
		if( !cb ) {
			cb = modifiers;
			modifiers = 0;
		}
		return $(this).keydown(function (e) {
			if( e.which === key ) {
				if( modifiers ) {
					if( bintest(modifiers, Modifier.CONTROL) && !e.ctrlKey ) {
						return;
					}
					if( bintest(modifiers, Modifier.ALT) && !e.altKey ) {
						return;
					}
					if( bintest(modifiers, Modifier.SHIFT) && !e.shiftKey ) {
						return;
					}
					if( bintest(modifiers, Modifier.META) && !e.metaKey ) {
						return;
					}
				}
				e.preventDefault();
				this.callback = cb;
				return this.callback(e);
			}
		});
	};
	
	$.fn.outerHTML = function () {
		return $('<div />').append(this.eq(0).clone()).html();
	};
	
	$(function () {
		$("input.autocomplete.auto").shown(function () {
			if( $(this).data("autocomplete-auto") ) {
				return;
			}
			$(this).data("autocomplete-auto", 1);
			var _ = $(this);
			_.autocomplete({
				minLength: 2,
				source: function (request, response) {
					var query = _.data("query") ? "&" + _.data("query") : "";
					// Targeting the autocomplete itself
					requestAutocomplete(_.data('what'), request.term + query, response, _.data("label"));
				}
			});
		});
		
		$('input[type=url]').watch(function () {
			var value = $(this).val();
			if( value.length && value.indexOf("://") < 0 ) {
				$(this).val(value = "http://" + value);
			}
			$($(this).data("linkbtn") || $(this).next("a")).attr("href", value);
		});
		
		$(document).on('click', '[data-toggle-class]', function () {
			let $actionner = $(this);
			let $target = $($actionner.data('toggleTarget'));
			if( $target.length > 1 ) {
				$target = $target.has($actionner);
			}
			$target.toggleClass($actionner.data('toggleClass'));
		});
		
		$('[data-form-change],[data-form-change-not]').each(function () {
			let $subject = $(this);
			// If any change in the form
			$subject.closest('form').find(':input').one('change', function () {
				$subject.removeClass($subject.data('formChangeNot')).addClass($subject.data('formChange'));
			});
		});
		
		$('.modal-focus').each(function () {
			let $dialog = $(this).closest('.modal');
			$dialog.on('shown.bs.modal', function () {
				let $form = $(this).getForm(true) || $dialog;
				if( !$.contains($dialog, $form) ) {
					// Can not select a form outside the dialog
					$form = $dialog;
				}
				let $focused = $form.find(':input:not(:button):visible').first();
				$focused.focus();
			})
		});
		
		$('.modal-form-reset').each(function () {
			let $element = $(this);
			let $dialog = $element.closest('.modal');
			$dialog.on('hidden.bs.modal', function () {
				try {
					console.log('Reset form');
					$dialog.resetForm();
				} catch( error ) {
					// Ignore missing form
				}
			})
		});
		
		$('.smart-enter-on-inputs :input:visible').pressEnter(function () {
			let $currentInput = $(this);
			let $parent = $currentInput.closest('.smart-enter-on-inputs');
			let $inputs = $parent.find(':input:visible');
			let $lastInput = $inputs.last();
			if( $currentInput.is($lastInput) ) {
				// $currentInput.getForm().submit();
			} else {
				let $nextInput = $inputs[($.inArray(val, $inputs) + 1) % $inputs.length];
				$nextInput.focus();
				return false;
			}
		});
		
		$('[data-enter]').each(function () {
			let $element = $(this);
			$element.pressEnter(function () {
				let action = $element.data('enter');
				if( action === 'click' ) {
					let $target = $($element.data('target'));
					$target.click();
				}
			});
		});
	});
	
	
})(jQuery);
import './orpheus-confirm-dialog.js';

// Export globals
global.t = t;
global.provideTranslations = provideTranslations;
