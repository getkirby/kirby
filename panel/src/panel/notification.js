import { reactive } from "vue";
import AuthError from "@/errors/AuthError.js";
import JsonRequestError from "@/errors/JsonRequestError.js";
import RequestError from "@/errors/RequestError.js";
import State from "./state.js";
import Timer from "./timer.js";

export const defaults = () => {
	return {
		context: null,
		details: null,
		icon: null,
		isOpen: false,
		message: null,
		theme: null,
		timeout: null,
		type: null
	};
};

/**
 * @since 4.0.0
 */
export default (panel = {}) => {
	const parent = State("notification", defaults());

	return reactive({
		...parent,

		/**
		 * Closes the notification by
		 * setting the inactive state (defaults)
		 *
		 * @returns {Object} The inactive state
		 */
		close() {
			// stop any previous timers
			this.timer.stop();

			// reset the defaults
			this.reset();

			// return the closed state
			return this.state();
		},

		/**
		 * Sends a deprecation warning to the console
		 *
		 * @param {String} message
		 */
		deprecated(message) {
			console.warn("Deprecated: " + message);
		},

		/**
		 * Converts an error object or string
		 * into an error notification
		 *
		 * @param {Error|Object|String} error
		 * @returns {Object} The notification state
		 */
		error(error) {
			if (error instanceof AuthError) {
				// only redirect to logout if panel state actually
				// assumes the user is authenticated, otherwise
				// continue to show a normal error notification
				if (panel.user.id) {
					return panel.redirect("logout");
				}
			}

			if (error instanceof JsonRequestError) {
				return this.fatal(error);
			}

			if (error instanceof RequestError) {
				// get the broken element in the response json that
				// has an error message. This can be deprecated later
				// when the server always sends back a simple error
				// response without nesting it in dropdown, dialog, etc.
				const broken = Object.values(error.response.json).find(
					(element) => typeof element?.error === "string"
				);

				if (broken) {
					error.message = broken.error;
					error.details = broken.details;
				}
			}

			// convert strings to full error objects
			if (typeof error === "string") {
				error = {
					message: error
				};
			}

			// turn instances into basic object and
			// fill in some fallback defaults
			error = {
				message: error.message ?? "Something went wrong",
				details: error.details ?? {}
			};

			// open the error dialog in views
			if (panel.context === "view") {
				panel.dialog.open({
					component: "k-error-dialog",
					props: error
				});
			}

			// show the error notification bar
			return this.open({
				message: error.message,
				icon: "alert",
				theme: "negative",
				type: "error"
			});
		},

		/**
		 * Shortcut to create an info
		 * notification. You can pass a simple
		 * string or a state object.
		 *
		 * @param {Object|String} info
		 * @returns {Object} The notification state
		 */
		info(info = {}) {
			if (typeof info === "string") {
				info = { message: info };
			}

			return this.open({
				icon: "info",
				theme: "info",
				...info
			});
		},

		/**
		 * Checks if the notification is a fatal
		 * error. Those are displayed in the <k-fatal>
		 * component which sends them to an isolated
		 * iframe. This will happen when API responses
		 * cannot be parsed at all.
		 */
		get isFatal() {
			return this.type === "fatal";
		},

		/**
		 * Creates a fatal error based on an
		 * Error object or string
		 *
		 * @param {Error|Object|String} error
		 * @returns {Object} The notification state
		 */
		fatal(error) {
			if (typeof error === "string") {
				return this.open({
					message: error,
					type: "fatal"
				});
			}

			if (error instanceof JsonRequestError) {
				return this.open({
					message: error.response.text,
					type: "fatal"
				});
			}

			return this.open({
				message: error.message ?? "Something went wrong",
				type: "fatal"
			});
		},

		/**
		 * Opens the notification
		 * The context will determine where it will
		 * be shown.
		 *
		 * @param {Error|Object|String} notification
		 * @returns {Object} The notification state
		 */
		open(notification) {
			// stop any previous timers
			this.timer.stop();

			// simple success notifications
			if (typeof notification === "string") {
				return this.success(notification);
			}

			// add timeout if not error or fatal notification
			if (notification.type !== "error" && notification.type !== "fatal") {
				notification.timeout ??= 4000;
			}

			// set the new state
			this.set({
				// add the current editing context
				context: panel.context,
				...notification
			});

			// open the notification
			this.isOpen = true;

			// start a timer to auto-close the notification
			this.timer.start(this.timeout, () => this.close());

			// returns the new open state
			return this.state();
		},

		/**
		 * Shortcut to create a success
		 * notification. You can pass a simple
		 * string or a state object.
		 *
		 * @param {Object|String} success
		 * @returns {Object} The notification state
		 */
		success(success = {}) {
			if (typeof success === "string") {
				success = { message: success };
			}

			return this.open({
				icon: "check",
				theme: "positive",
				...success
			});
		},

		/**
		 * Holds the timer object
		 */
		timer: Timer
	});
};
