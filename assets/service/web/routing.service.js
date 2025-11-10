class RoutingService {
	
	constructor() {
		this.routes = {};
	}
	
	addRoute(route, url) {
		this.routes[route] = url;
	}
	
	generate(route, parameters) {
		let url = this.routes[route];
		if( parameters ) {
			Object.entries(parameters).forEach(([key, value]) => {
				url = url.replace(encodeURI('{ ' + key + ' }'), value);
			});
		}
		return url;
	}
	
}

export const routingService = new RoutingService();
global.routingService = routingService;
