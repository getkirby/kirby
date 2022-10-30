import store from "./store.js";

export default {
	install(app) {
		// polyfill: module's state
		store.state.content = new Proxy(store.content.state, {});
		store.state.drawers = new Proxy(store.drawers.state, {});
		store.state.notification = new Proxy(store.notification.state, {});

		// polyfill: getters and dispatch actions
		const base = (name) => {
			name = name.split("/");
			let base = store;

			for (const part of name) {
				base = base[part];
			}

			return base;
		};

		store.dispatch = (event, args) => base(event)(args);
		store.getters = new Proxy(
			{},
			{
				get(target, property) {
					return base(property);
				}
			}
		);

		// Vue shortcuts
		window.panel.$store = app.prototype.$store = store;
		window.panel.$drawers = app.prototype.$drawers = store.drawers;
		window.panel.$nofitication = app.prototype.$nofitication =
			store.nofitication;
	}
};
