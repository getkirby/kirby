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

		/**
		 * Setup the content store
		 */
		this.$store.dispatch("content/init");
	},
	destroyed() {
		this.$panel.events.unsubscribe();
	},
	render(h) {
		if (this.$panel.view.component) {
			return h(this.$panel.view.component, {
				key: this.$panel.view.component,
				props: this.$panel.view.props
			});
		}
	}
};
