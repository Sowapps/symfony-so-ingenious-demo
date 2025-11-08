export class Process {
	
	/**
	 * @param {int} delay Delay in milliseconds
	 * @return {Promise<unknown>}
	 */
	static async wait(delay) {
		return new Promise(r => setTimeout(r, delay));
	}
	
}
