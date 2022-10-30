import { startStimulusApp } from '@symfony/stimulus-bridge';
// import SoCoreFileController from '../vendor/sowapps/so-core/assets/controllers/input/file_controller.js';

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
	'@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
	true,
	/\.[jt]sx?$/
));

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

// SoCore Bundle
// app.register('sowapps--so-core--input-file', SoCoreFileController);


