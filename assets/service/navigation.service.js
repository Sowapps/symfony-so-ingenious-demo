import { Portrait } from "../core/trait/Portrait.js";
import { EventListenerTrait } from "../core/event/EventListenerTrait.js";
import { StringTemplate } from "../core/StringTemplate.js";
import { AbstractMainController } from "../core/controller/controllers.js";
import { Is } from "../helpers/is.helper.js";

class NavigationService {
	/** @var {Route[]} */
	routes = [];
	
	filters = {};
	/** @var {Request} */
	currentRequest = null;

	/**
	 * Navigate to controller page (destination must be in main controller's routes)
	 * Require to be route handled by this main controller
	 *
	 * @param {string} path
	 * @param {Object} parameters
	 * @return {Promise<void>}
	 */
	async navigate(path, parameters = {}) {
		if( !path ) {
			throw new Error("Unable to navigate to empty path");
		}
		if( path instanceof Route ) {
			path = path.path;
		}
		console.info("Navigate to", path, "with parameters", parameters);
		// Change url
		this.setUrlPath(path, parameters);
		// Url changed but that's all, main controller should handle the changes in page
		await this.trigger(NavigationEvent.NAVIGATED, {path, parameters});
	}

	getCurrentUrl() {
		return new URL(window.location.href);
	}

	/**
	 * Force real http redirection
	 *
	 * @param {string} destination
	 */
	redirectTo(destination) {
		window.location.href = destination;
	}
	
	/**
	 * Force to refresh by reloading current location
	 */
	reload() {
		window.location.reload();
	}

	/**
	 * @param {URL|null} url
	 * @return {Request|null}
	 */
	getRequest(url = null) {
		url = url || this.getCurrentUrl();
		const routeParams = this.findFirstMatchingRoute(url.pathname);
		if( !routeParams ) {
			throw new Error(`No route found for request path '${url.pathname}'`);
		}
		return new Request(routeParams.route, routeParams.parameters, url.pathname, Object.fromEntries(url.searchParams.entries()));
	}

	/**
	 * @param {String} path There request path (with parameters)
	 * @return {{route: Route, parameters: Object}|null}
	 */
	findFirstMatchingRoute(path) {
		for( const route of this.routes ) {
			const parameters = this.getPathRouteParameters(route, path);
			if( parameters ) {
				return {route, parameters};
			}
		}

		return null;
	}

	/**
	 * @param {Route} route
	 * @param {string} path
	 * @return {Object|null}
	 */
	getPathRouteParameters(route, path) {
		const {regex, parameters} = route.pathRegex;
		let pathParameterList = regex.exec(path);
		if( !pathParameterList ) {
			return null;
		}
		pathParameterList = pathParameterList.slice(1);// Remove first match, the whole string
		if( parameters.length !== pathParameterList.length ) {
			console.warn(`Issue with route ${route.path} and path ${path}, regex did not find the right count of path parameters (${pathParameterList.length} but got ${parameters.length})`);
			return null;
		}
		const pathParameters = {};
		for( const [index, name] of parameters.entries() ) {
			pathParameters[name] = pathParameterList[index];
		}

		return pathParameters;
	}
	
	getCurrentStateRequest() {
		// Convert serializable object to Request from history state
		const requestObject = history.state?.request;
		return requestObject ? Request.fromObject(requestObject) : null;
	}

	/**
	 * @param {Request|null} request
	 */
	setCurrentStateRequest(request) {
		const state = history.state;
		// Convert Request to serializable object for history state
		state.request = request.toObject();
		console.log("Serializable state.request", state.request);
		this.#setCurrentState(state);
	}

	#setCurrentState(data) {
		history.replaceState(data, "");
	}

	/**
	 * @param {string} path
	 * @param {Object} parameters
	 */
	setUrlPath(path, parameters = {}) {
		const stringTemplate = new StringTemplate(this.filters, this);
		const formattedUrl = stringTemplate.render(path, parameters);
		const state = history.state;
		state.request = null;
		state.route = {path: path, parameters: parameters};
		console.info(`Set url to ${formattedUrl} with state`, state);
		// Set real url in browser with new history entry
		history.pushState(state, "", formattedUrl);
	}
	
	/**
	 * @param {Route[]} routes
	 */
	registerRoutes(routes) {
		this.routes = routes;
	}

}

Portrait.for(NavigationService).use(EventListenerTrait);
/**
 * @name NavigationService#on
 * @function
 * @memberof NavigationService
 * @param {String } event The event to listen
 * @return {DeferredPromise}
 */
/**
 * @name NavigationService#off
 * @function
 * @memberof NavigationService
 * @param {string|DeferredPromise} promiseOrEvent The event to unbind
 */
/**
 * @name NavigationService#trigger
 * @function
 * @memberof NavigationService
 * @param {String} event
 * @param {Object|any|null} data
 */

export const NavigationEvent = {
	// REQUEST: "navigation.request",
	NAVIGATED: "navigation.navigated",
};

export const navigationService = new NavigationService();

class Route {
	path;
    navigationRequirement;
	#menuPath;

    /**
     * @param {String} path
     * @param {String|null} navigationRequirement
     */
	constructor(path, navigationRequirement = null) {
		if( this.constructor === Route ) {
			throw new TypeError("Abstract class \"Route\" cannot be instantiated directly");
		}

		this.path = path;
		this.navigationRequirement = navigationRequirement;
		this.#menuPath = null;
	}

	/**
	 * @param {AbstractMainController} controller
	 * @param {Request} request
	 * @return {Promise<void>}
	 */
	applyTo(controller, request) {
		throw new Error("You must implement this function");
	}

	get pathRegex() {
		const parameters = [];
		const regexPath = this.path.replace(/\{([^\}]+)\}/g, (match, variable) => {
			parameters.push(variable);
			return "([^/]+)"; // Capture tout sauf le séparateur de chemin "/"
		});
		return {regex: new RegExp("^" + regexPath + "$"), parameters};
	}

	get menuPath() {
		return this.#menuPath || this.path;
	}

	setMenuPath(path) {
		this.#menuPath = path;

		return this;
	}
}

export class TemplateRoute extends Route {
	template;

    /**
     * @param {String} path
     * @param {String} template
     * @param {String|null} navigationRequirement
     */
	constructor(path, template, navigationRequirement = null) {
		super(path, navigationRequirement);
		this.template = template;
	}

	/**
	 * @param {AbstractMainController} controller
	 * @param {Request} request
	 * @return {Promise<void>}
	 */
	applyTo(controller, request) {
		return controller.setContentsToTemplate(this.template, request.getAllValues());
	}
}

export class CallbackRoute extends Route {
	callback;

	/**
     * @param {String} path
     * @param {Function} callback
     * @param {String|null} navigationRequirement
     */
	constructor(path, callback, navigationRequirement = null) {
		super(path, navigationRequirement);
		this.callback = callback;
	}

	/**
	 * @param {AbstractMainController} controller
	 * @param {Request} request
	 * @return {Promise<void>}
	 */
	applyTo(controller, request) {
		return this.callback.call(controller, request);
	}
}

export class Request {
	
	/**
	 * @param {Route} route
	 * @param {Object} parameters
	 * @param {string} path
	 * @param {Object} query
	 */
	constructor(route, parameters, path, query) {
		/** @var {Route} route Calculated route from request */
		this.route = route;
		/** @var {Object} parameters Calculated parameters from request */
		this.parameters = parameters;
		/** @var {string} path Real path from request URI */
		this.path = path;
		/** @var {Object} query Query string from request URI */
		this.query = query;
	}
	
	// /**
	//  * @return {string}
	//  */
	// getUri() {
	// 	return this.path + this.getQueryString();
	// }
	
	getQueryString() {
		if( Is.empty(this.query) ) {
			return "";
		}
		const params = new URLSearchParams(this.query);
		return params.toString();
	}
	
	/**
	 * @return {Object}
	 */
	getAllValues() {
		return {...this.query, ...this.parameters};
	}
	
	/**
	 * To serializable object
	 * @return {Object}
	 */
	toObject() {
		// Flat object
		const object = Object.assign({}, this);
		delete object.route;
		return object;
	}
	
	/**
	 * From serializable object
	 * @param {Object} object Flat object
	 */
	static fromObject(object) {
		const routeParams = navigationService.findFirstMatchingRoute(object.path);
		if( !routeParams ) {
			throw new Error("Unable to format Request from serialized object : Route not found");
		}
		return new Request(routeParams.route, object.parameters, object.path, object.query);
	}
	
}
