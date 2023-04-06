/**
 * This is the application component
 * that will be mounted by Vue. It renders
 * the current view and takes care of
 * registering all global events and plugins
 */
export default {
	created() {
		/**
		 * Delegate all required window events to the
		 * event emitter
		 */
		this.$panel.events.subscribe();

		/**
		 * Register all created plugins
		 */
		this.$panel.plugins.created.forEach((plugin) => {
			plugin(this);
		});

		/**
		 * Hook up the back button
		 */
		this.$panel.events.on("popstate", () => {
			this.$panel.open(window.location.href);
		});

		/**
		 * Clean drag & drop info
		 */
		this.$panel.events.on("drop", () => {
			this.$store.dispatch("drag", null);
		});
	},
	/**
	 * Removes all global event listeners
	 */
	destroyed() {
		this.$panel.events.unsubscribe();
	},
	/**
	 * Render the current view component
	 * with the props and timestamp as key
	 * to make sure it's automatically re-rendered
	 * if there's a new version from the server
	 */
	render(h) {
		if (this.$panel.view.component) {
			return h(this.$panel.view.component, {
				key: this.$panel.view.component,
				props: this.$panel.view.props
			});
		}
	}
};
