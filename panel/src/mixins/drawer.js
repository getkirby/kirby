/**
 * The Drawer mixin is intended for all components
 * that extend <k-drawer> It forwards the methods to
 * the <k-drawer> ref. Extending <k-drawer> directly
 * can lead to breaking methods when the methods are not
 * wired correctly to the right elements and refs.
 */
export default {
	props: {
		icon: String,
		id: String,
		options: {
			type: Array
		},
		tabs: {
			default: () => {},
			type: [Array, Object]
		},
		title: String,
		visible: {
			default: false,
			type: Boolean
		}
	},
	methods: {
		/**
		 * Triggers the `@cancel` event and closes the drawer.
		 * @public
		 */
		cancel() {
			this.$panel.drawer.cancel();
		},
		/**
		 * Triggers the `@close` event and closes the drawer.
		 * @public
		 */
		close() {
			this.$panel.drawer.close();
		},
		/**
		 * Shows the error notification bar in the drawer with the given message
		 * @public
		 * @param {String} error
		 */
		error(error) {
			this.$panel.drawer.error(error);
		},
		/**
		 * Sets the focus on the first usable input
		 * or a given input by name
		 * @public
		 * @param {String} input
		 */
		focus(input) {
			this.$panel.drawer.focus(input);
		},
		/**
		 * Hides the overlay. This should only be used
		 * in the island code to support inline components
		 *
		 * @private
		 */
		hide() {
			this.$refs.drawer.hide();
		},
		/**
		 * Updates the drawer values
		 * @public
		 * @param {Object} value
		 */
		input(value) {
			this.$panel.drawer.input(value);
		},
		/**
		 * Opens the drawer and triggers the `@open` event.
		 * Use ready to fire events that should be run as
		 * soon as the drawer is open
		 * @public
		 */
		open() {
			this.$panel.drawer.open(this);
		},
		/**
		 * When the overlay is open and fully usable
		 * the ready event is fired and forwarded here
		 * @public
		 */
		ready() {
			this.$panel.drawer.emit("ready");
		},
		/**
		 * Shows the overlay. This should only be used
		 * in the island code to support inline components
		 *
		 * @private
		 */
		show() {
			this.$refs.drawer.show();
		},
		/**
		 * This event is triggered when the submit button is clicked,
		 * or the form is submitted. It can also be called manually.
		 * @public
		 */
		submit() {
			this.$panel.drawer.submit(this.$panel.value);
		},
		/**
		 * Shows the success notification bar in the drawer with the given message
		 * @public
		 * @param {String|Object} message
		 */
		success(success) {
			this.$panel.drawer.success(success);
		}
	}
};
