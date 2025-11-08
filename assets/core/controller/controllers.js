import { Controller } from "@hotwired/stimulus";
import { domService } from "../../services/dom.service.js";
import { Exception } from "../exceptions.js";
import * as bootstrap from "../../vendor/bootstrap/bootstrap.index.js";
import { sawService } from "../../services/saw.service.js";
import { NavigationEvent, navigationService } from "../../services/navigation.service.js";
import { SecurityEvent, securityService } from "../../services/security.service.js";
import { ApiVersionError, appWebService } from "../../services/app-web.service.js";
import { consoleService } from "../../services/web/console.service.js";
import { localStorageService } from "../../services/web/local-storage.service.js";

/**
 * @property {Element} contentTarget
 * @property {Element} userLabelTarget
 * @property {Boolean} hasUserLabelTarget
 * @property {Element} userMenuTarget
 * @property {Element} notificationListTarget
 * @property {Element} templateNotificationErrorTarget
 * @property {Element} templateNotificationSuccessTarget
 * @property {Element} versionMismatchModalTarget
 * @property {Boolean} hasVersionMismatchModalTarget
 * @property {Number} versionWarningDelayValue
 */
export class AbstractMainController extends Controller {
    static targets = ["content", "userLabel", "userMenu", "notificationList", "templateNotificationError", "templateNotificationSuccess", "versionMismatchModal"];
    static values = {
        versionWarningDelay: {type: Number, default: 120000},
    };
    
    REQ_USER_AUTHENTICATED = 'USER_AUTHENTICATED';

    navigationRequirements = {};// Route may require something to start
    routes = [];
    promises = [];
    /** @type {Route} */
    loginRoute = null;
    clientVersion;
    outdatedApp = false;

    initialize() {
        // App start
        consoleService.includeServices();
        localStorageService.setPrefix("slg.");
        this.clientVersion = document.querySelector("meta[name=\"app-version\"]").content;
        appWebService.frontVersion = this.clientVersion;
    }

    async connect() {
        document.documentElement.dataset.bsTheme = "dark";
        sawService.setDefaultVar("mainController", this.identifier);
        
        // Components
        this.versionMismatchModal = this.hasVersionMismatchModalTarget ? new bootstrap.Modal(this.versionMismatchModalTarget) : null;
        
        // Routing
        navigationService.registerRoutes(this.routes);
        this.watchNavigation();
        this.watchUserAuthentication();
        this.watchDocumentClick();
        this.registerNavigationRequirement(this.REQ_USER_AUTHENTICATED, routeRequest => this.requireAuthenticatedNavigation(routeRequest));

        const user = await this.loadSession();
        if( user ) {
            this.triggerUserConnected(user);
        }
        await this.navigateFromCurrentUrl();
    }

    async disconnect() {
        navigationService.off(NavigationEvent.NAVIGATED);
        securityService.off(SecurityEvent.USER_AUTHENTICATED);
        securityService.off(SecurityEvent.USER_CONNECTED);
        securityService.off(SecurityEvent.USER_DISCONNECTED);
        this.unbindPromises();
    }

    /**
     * Ensure user is authenticated to navigate to this route else it display the login form before accessing target route
     *
     * @param {Object} targetRoute
     * @param {Request} targetRoute.route
     * @param {Object} targetRoute.parameters
     */
    requireAuthenticatedNavigation({route, parameters}) {
        if( securityService.isAuthenticated() ) {
            return true;// Allow to continue
        }
        parameters.targetPath = route.path;
        navigationService.navigate(this.loginRoute.path, parameters);

        // this.navigateFromRoute(this.loginRoute, parameters); // Implicit controller redirection
        return false;
    }

    /**
     * @param {String} requirement
     * @param {CallableFunction} handler
     */
    registerNavigationRequirement(requirement, handler) {
        this.navigationRequirements[requirement] = {
            name: requirement,
            handler: handler // Return true to continue, navigation is approved
        }
    }

    async navigateFromCurrentUrl() {
        console.log("navigateFromCurrentUrl - history.state", Object.assign({}, history.state));
        let request = navigationService.getCurrentStateRequest();
        if( !request ) {
            request = navigationService.getRequest();
            navigationService.setCurrentStateRequest(request);
        } else {
            console.info("Restored state route", request);
        }
        
        await this.navigateFromRequest(request);
    }
    
    /**
     * @param {Request} request
     * @return {Promise<void>}
     */
    async navigateFromRequest(request) {
        console.info("navigateFromRequest", request);
        // Navigation requirement (e.g. user must be authenticated, so we go to the login page first)
        const route = request.route;
        if( route.navigationRequirement ) {
            const navigationRequirement = this.navigationRequirements[route.navigationRequirement];
            if( !navigationRequirement ) {
                throw new Error(`Route has unknown requirement ${route.navigationRequirement} for route ${route.path} (${typeof route})`);
            }
            if( !navigationRequirement.handler(request) ) {
                // Abort this navigation
                return;
            }
        }
        
        navigationService.currentRequest = request;
        
        await route.applyTo(this, request);
        this.refreshLayoutMenus();
    }

    onNavigationChanged(path, parameters) {
        console.info("onNavigationChanged", path, parameters);
        return this.navigateFromCurrentUrl();
    }

    onUserAuthenticated(user) {
        this.triggerUserConnected(user);
    }

    onUserConnected(user) {
        securityService.user = user;
        this.refreshContents();
    }

    /**
     * @param {Object|null} user The disconnected user (or null if arriving not authenticated)
     */
    onUserDisconnected(user) {
        // Do nothing
    }

    triggerUserConnected(user) {
        securityService.trigger(SecurityEvent.USER_CONNECTED, {user: user});
    }

    async loadSession() {
        // Load current user
        try {
            return await appWebService.getAuthenticatedUser();
        } catch (exception) {
            console.error("Error getting authenticated user", exception);
            return null;
        }
    }

    async logout() {
        await appWebService.disconnectUser();
        securityService.revokeUser();
    }

    watchNavigation() {
        navigationService.on(NavigationEvent.NAVIGATED)
            .then(event => this.onNavigationChanged(event.data.path, event.data.parameters));
    }

    watchUserAuthentication() {
        securityService.on(SecurityEvent.USER_AUTHENTICATED)
            .then(event => this.onUserAuthenticated(event.data.user));
        securityService.on(SecurityEvent.USER_CONNECTED)
            .then(event => this.onUserConnected(event.data.user));
        securityService.on(SecurityEvent.USER_DISCONNECTED)
            .then(event => this.onUserDisconnected(event.data.user));
    }

    watchDocumentClick() {
        // Must capture children event too because document capture event once and target is the clicked element, not the clicked link
        this.promises.push(domService.on("a, a *", "click")
            .then(event => {
                const target = domService.queryMeOrParent(event.target, "a");
                const targetUrl = target.href && new URL(target.href);
                const currentUrl = navigationService.getCurrentUrl();

                if( targetUrl.origin === currentUrl.origin ) {
                    try {
                        if( (targetUrl.pathname + targetUrl.search) !== (currentUrl.pathname + currentUrl.search) ) {
                            // Navigate if target is not the current page
                            navigationService.navigate(targetUrl.pathname);
                            event.preventDefault();
                        } else {
                            throw new Error("Invalid website information");
                        }
                    } catch( error ) {
                        // Page was not found, this is out of perimeter, we should go to this page as usual
                        console.log("User request navigation to request and failed", error);
                    }
                } // Else let it go to another website
            }));
    }

    unbindPromises() {
        this.promises.forEach(promise => domService.off(promise));
    }

    /**
     * @param templatePath
     * @param templateParams
     * @return Promise<void>
     */
    async setContentsToTemplate(templatePath, templateParams = {}) {
        await sawService.assignTemplate(this.contentTarget, templatePath, templateParams);
        this.refreshContents();
    }

    refreshLayoutMenus() {
        // Do nothing
    }

    refreshMenu(menuElement) {
        const route = navigationService.currentRequest.route;
        const currentUrl = navigationService.getCurrentUrl();
        // Find active menu item
        const menuItems = menuElement.querySelectorAll("a.nav-link");
        let activeItem = null;
        for (const menuItem of menuItems) {
            // Reset menu item
            menuItem.classList.remove("active");
            menuItem.ariaCurrent = null;
            // Compare URL
            const menuItemUrl = new URL(menuItem.href);
            if( menuItemUrl.origin === currentUrl.origin && menuItemUrl.pathname === route.menuPath ) {
                if( activeItem && menuItemUrl.search ) {
                    // Handle menu item with query string (when there is an usage)
                } else {
                    activeItem = menuItem;
                }
            }
        }
        // Set menu item active
        if( activeItem ) {
            activeItem.classList.add("active");
            activeItem.ariaCurrent = "page";
        }
    }

    refreshContents() {
        // Default behavior

        // Show user menu if authenticated
        if( !this.hasUserLabelTarget ) {
            // Loading page, user is loaded but page is not yet
            return;
        }
        const authenticatedUser = securityService.user;
        const isAuthenticated = !!authenticatedUser;

        // Update user menu
        this.userLabelTarget.innerText = isAuthenticated ? authenticatedUser.name : "";
        this.userMenuTarget.hidden = !isAuthenticated;
    }
    
    showSuccess(event) {
        this.pushNotificationSuccess(event.detail.message, event.detail.options);
    }

    showError(event) {
        const error = event.detail.error;
        if( error instanceof ApiVersionError && !this.outdatedApp ) {
            console.error("Fatal error, " + error.getMessage());
            this.outdatedApp = true;
            playerHeritageService.stop(true);
            this.versionMismatchModal.show();// Never close, force to reload page
            setTimeout(() => this.reloadPage(), this.versionWarningDelayValue);// Env
        }
        this.pushNotificationError(error, event.detail.options);
    }
    
    reloadPage() {
        console.info("Force reloading page");
        navigationService.reload();
    }

    pushNotificationError(error, options) {
        const output = {title: "System", message: error instanceof Exception ? error.getMessage() : error};
        const notificationElement = domService.renderTemplate(this.templateNotificationErrorTarget, output)[0];
        this.#prePushNotification(notificationElement, options);
        this.pushNotification(notificationElement, this.#formatToastOptions(options));
    }
    
    pushNotificationSuccess(message, options) {
        const output = {title: "System", message: message};
        const notificationElement = domService.renderTemplate(this.templateNotificationSuccessTarget, output)[0];
        this.#prePushNotification(notificationElement, options);
        this.pushNotification(notificationElement, this.#formatToastOptions(options));
    }
    
    #prePushNotification(notificationElement, options) {
        if( options.channel ) {
            // With channel, we can only show one of this channel at a time
            const channelClass = "notification-channel-" + options.channel;
            notificationElement.classList.add(channelClass);
            const existing = this.notificationListTarget.querySelector("." + channelClass);
            if( existing ) {
                existing.remove();
            }
        }
    }
    
    #formatToastOptions(options) {
        options = options || {};
        // @see https://getbootstrap.com/docs/5.3/components/toasts/#options
        const toastOptions = {autohide: false};
        if( options.autoHide ) {
            toastOptions.autohide = true;
            // use default delay
        }
        if( options.hideDelay ) {
            toastOptions.autohide = true;
            toastOptions.delay = parseInt(options.hideDelay);// Milliseconds
        }
        
        return toastOptions;
    }

    /**
     * @param {Element} notificationElement
     * @param options
     */
    pushNotification(notificationElement, options) {
        this.notificationListTarget.append(notificationElement);
        const toast = new bootstrap.Toast(notificationElement, options);
        toast.show();
    }

}

/**
 * @member {Element[]} formTargets
 */
export class AbstractPageController extends Controller {
    submittingForm = null;

    /**
     * Set form to submitting state, every field is disabled
     *
     * @param form
     * @param defaults
     * @return {{}}
     */
    startSubmittingForm(form, defaults = {}) {
        this.submittingForm = form;
        const input = Object.assign(defaults, domService.getFormObject(form));
        this.disableAllForms();

        return input;
    }

    /**
     * Remove form from submitting state, every field is enabled again
     */
    endSubmittingForm() {
        this.submittingForm = null;
        this.enableAllForms();
    }

    enableAllForms() {
        this.formTargets.forEach(form => domService.enableForm(form));
    }

    disableAllForms() {
        this.formTargets.forEach(form => domService.disableForm(form));
    }

    async report(container, type, message) {
        const [reportElement] = domService.renderTemplate(document.getElementById("TemplateAlert"), {type, message});
        container.replaceChildren(reportElement);
        container.hidden = false;
        await domService.fadeOut(reportElement, 10000, true);
        if( !container.hasChildNodes() ) {
            container.hidden = true;
        }
    }

    dispatchEvent(event, detail = null, options = {}) {
        domService.dispatchEvent(this.element, event, detail, options);
    }

    reportException(exception, options) {
        this.dispatchEvent("app.error", {error: exception, options});
    }
    
    reportSuccess(message, options) {
        this.dispatchEvent("app.success", {message, options});
    }

}
