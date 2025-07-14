import { h, resolveComponent } from "vue";

/**
 * @since 4.0.0
 */
export default {
	computed: {
		component() {
			return this.$panel.view.component;
		},
		view() {
			return this.$panel.view.props;
		}
	},
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
	unmounted() {
		this.$panel.events.unsubscribe();
	},
	render() {
		if (this.component) {
			return h(resolveComponent(this.component), {
				key: this.component,
				...this.view
			});
		}
	}
};
