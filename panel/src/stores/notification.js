export default {
	/**
	 * State
	 */
	state: {
		type: null,
		message: null,
		details: null,
		timeout: null,
		timer: null
	},

	/**
	 * Actions
	 */
	set(notification) {
		this.state.type = notification.type;
		this.state.message = notification.message;
		this.state.details = notification.details;
		this.state.timeout = notification.timeout;
	},
	clear() {
		this.state.type = null;
		this.state.message = null;
		this.state.details = null;
		this.state.timeout = null;
	},

	close() {
		clearTimeout(this.state.timer);
		this.clear();
	},
	deprecated(message) {
		console.warn("Deprecated: " + message);
	},
	error(error) {
		// props for the dialog
		let props = error;

		// handle when a simple string is thrown as error
		// we should avoid that whenever possible
		if (typeof error === "string") {
			props = {
				message: error
			};
		}

		// handle proper Error instances
		if (error instanceof Error) {
			// convert error objects to props for the dialog
			props = {
				message: error.message
			};

			// only log errors to the console in debug mode
			if (window.panel.$config.debug) {
				window.console.error(error);
			}
		}

		// show the error dialog
		window.panel.$store.dialog(
			"dialog",
			{
				component: "k-error-dialog",
				props: props
			},
			{ root: true }
		);

		// remove the notification from store
		// to avoid showing it in the topbar
		this.close();
	},
	open(notification) {
		this.close();
		this.set(notification);

		if (notification.timeout) {
			this.timer = setTimeout(() => {
				this.close;
			}, notification.timeout);
		}
	},
	success(notification) {
		if (typeof notification === "string") {
			notification = { message: notification };
		}

		this.open({
			type: "success",
			timeout: 4000,
			...notification
		});
	}
};
