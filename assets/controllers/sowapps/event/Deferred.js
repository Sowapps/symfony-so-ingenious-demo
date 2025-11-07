import { stringService } from "../../../vendor/orpheus/js/service/string.service.js";

export class Deferred {
	/**
	 * @type {DeferredPromise}
	 */
	#promise
	
	constructor(type) {
		this.type = type;
		this.doneCallback = null;
		this.failCallback = null;
		// For now, we want to lose previous values
		// this.pendingValue = null;
		
		// Bidirectional
		this.children = [];
		this.parent = null;
		// Unique ID
		this.id = stringService.generateId();
		this.#promise = new DeferredPromise(this);
	}
	
	/**
	 * @param {Array<DeferredPromise>} promises
	 * @return Deferred
	 */
	static any(promises) {
		const deferred = new Deferred(promises.length ? promises[0].type() : null);
		promises.forEach(promise => promise.then(
			data => deferred.resolve(data),
			data => deferred.reject(data)
		));
		return deferred;
	}
	
	attachCallbacks(doneCallback, failCallback) {
		this.doneCallback = doneCallback;
		this.failCallback = failCallback;
		// if( this.pendingValue ) {
		// 	// Resolve now a previous resolved value
		// 	this.resolve(this.pendingValue);
		// }
	}
	
	reject(value) {
		if( this.failCallback ) {
			value = this.failCallback.apply(null, [value]);
		}
		if( this.children ) {
			this.children.forEach(child => child.reject(value));
		}
		
		return this;
	}
	
	promise() {
		return this.#promise;
	}
	
	getRootDeferred() {
		return this.parent ? this.parent.getRootDeferred() : this;
	}
	
	castChild() {
		const child = new Deferred(this.type);
		child.parent = this;
		this.children.push(child);
		return child;
	}
	
	getListener() {
		if( !this.listener ) {
			this.listener = event => {
				// console.log('Received event', event, 'with details', event.detail);
				this.resolve(event.detail, event);
			};
		}
		return this.listener;
	}
	
	resolve(...values) {
		// Root deferred never has callback, children are wearing it
		// Each promise then, each callback assignment is creating a new deferred child
		// let resolved = false;
		// if(!this.parent && !this.doneCallback && !this.children.length) {
		// 	// No resolutio
		// 	this.pendingValue =
		// }
		if( this.doneCallback ) {
			const value = this.doneCallback.apply(null, values);
			if( value !== undefined ) {
				values[0] = value;
			}
		}
		this.children.forEach(child => child.resolve(...values));
		
		return this;
	}
}

export class DeferredPromise {
	/**
	 * @param {Deferred} deferred
	 */
	constructor(deferred) {
		this.deferred = deferred;
	}
	
	type() {
		return this.deferred.type;
	}
	
	getRootDeferred() {
		return this.deferred.getRootDeferred();
	}
	
	then(doneCallback, failCallback) {
		const child = this.deferred.castChild();
		child.attachCallbacks(doneCallback, failCallback);
		return child.promise();
	}
}
