import Events from "@/panel/events.js";

/**
 * This is just a temporary
 * implementation to keep the old event bus
 * until window.panel is fully implemented
 */
export default {
	install(app) {
		const events = Events();

		events.subscribe();

		app.prototype.$events = events;
	}
};
