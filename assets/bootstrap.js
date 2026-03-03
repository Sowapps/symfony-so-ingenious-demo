import {startStimulusApp} from '@symfony/stimulus-bundle';
import {getApp} from '@sowapps/so-core';

const stimulusApp = startStimulusApp();
const app = getApp();
app.setStimulusApp(stimulusApp).start();

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
