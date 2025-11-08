/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

class LocalStorageService {
	
	prefix;
	
	/**
	 * @return {string}
	 */
	getPrefix() {
		return this.prefix ?? "";
	}
	
	/**
	 * @param {string} prefix
	 */
	setPrefix(prefix) {
		if( this.prefix !== undefined ) {
			throw new Error("Prefix is already defined and can not be overridden");
		}
		
		this.prefix = prefix;
	}
	
	/**
	 * @param {String} name
	 * @param {any} value
	 */
	set(name, value) {
		localStorage.setItem(this.getPrefix() + name, JSON.stringify(value));
	}
	
	/**
	 * @param {String} name
	 * @param {any} defaultValue
	 */
	get(name, defaultValue = null) {
		const value = localStorage.getItem(this.getPrefix() + name);
		if( value !== null ) {
			return JSON.parse(value);
		}
		return defaultValue;
	}
	
	/**
	 * @param {String} name
	 */
	remove(name) {
		localStorage.removeItem(name);
	}
	
}

export const localStorageService = new LocalStorageService();
