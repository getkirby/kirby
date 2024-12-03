/**
 * @since 4.0.0
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
		for (const plugin of this.$panel.plugins.created) {
			plugin(this);
		}

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
			this.$panel.drag.stop();
		});
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
