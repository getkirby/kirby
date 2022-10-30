import { reactive } from "vue";

// modules
import content from "./content.js";
import drawers from "./drawers.js";
import notification from "./notification.js";

export default reactive({
	/**
	 * Modules
	 */
	content,
	drawers,
	notification,

	/**
	 * Root state
	 */
	state: {
		dialog: null,
		drag: null,
		fatal: false,
		isLoading: false
	},

	/**
	 * Root actions
	 */
	dialog(dialog) {
		this.state.dialog = dialog;
	},
	drag(drag) {
		this.state.drag = drag;
	},
	fatal(options) {
		// close the fatal window if false
		// is passed as options
		if (options === false) {
			this.state.fatal = false;
			return;
		}

		console.error("The JSON response could not be parsed");

		// show the full response in the console
		// if debug mode is enabled
		if (window.panel.$config.debug) {
			console.info(options.html);
		}

		// only show the fatal dialog if the silent
		// option is not set to true
		if (!options.silent) {
			this.state.fatal = options.html;
		}
	},
	isLoading(loading) {
		this.state.isLoading = loading === true;
	},
	navigate() {
		this.dialog(null);
		this.drawers.close();
	}
});
