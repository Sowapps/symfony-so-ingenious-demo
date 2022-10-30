class TranslationService {
	
	constructor() {
		this.strings = {};
	}
	
	provide(key, text) {
		this.strings[key] = text;
		
		return this;
	}
	
	provideSet(strings) {
		this.strings = {...this.strings, ...strings};
		
		return this;
	}
	
	translate(key, parameters) {
		console.log('key', key, 'parameters', parameters);
		let text = this.strings[key];
		if( parameters ) {
			Object.entries(parameters).forEach(([key, value]) => {
				// console.log('Replace', '{' + key + '}', 'with', value);
				text = text.replace('{' + key + '}', value);
			});
		}
		return text;
	}
	
}

export const translationService = new TranslationService();
global.translationService = global.translation = translationService;
