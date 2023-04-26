import { props as Buttons } from "@/components/Dialogs/Elements/Buttons.vue";

export const props = {
	mixins: [Buttons],
	props: {
		size: {
			default: "default",
			type: String
		},
		visible: {
			default: false,
			type: Boolean
		}
	}
};

/**
 * The Dialog mixin is intended for all components
 * that extend <k-dialog> It forwards the methods to
 * the <k-dialog> ref. Extending <k-dialog> directly
 * can lead to breaking methods when the methods are not
 * wired correctly to the right elements and refs.
 */
export default {
	mixins: [props],
	methods: {
		/**
		 * Triggers the `@cancel` event and closes the dialog.
		 * @public
		 */
		cancel() {
			this.$panel.dialog.cancel();
		},
		/**
		 * Triggers the `@close` event and closes the dialog.
		 * @public
		 */
		close() {
			this.$panel.dialog.close();
		},
		/**
		 * Shows the error notification bar in the dialog with the given message
		 * @public
		 * @param {string} error
		 */
		error(error) {
			this.$panel.dialog.error(error);
		},
		/**
		 * The overlay component has a built-in focus
		 * method that finds the best first element to
		 * focus on
		 * @public
		 */
		focus(input) {
			this.$panel.dialog.focus(input);
		},
		/**
		 * Updates the dialog values
		 * @public
		 * @param {Object} value
		 */
		input(value) {
			this.$panel.dialog.input(value);
		},
		/**
		 * Opens the overlay and triggers the `@open` event
		 * Use the `ready` event to
		 * @public
		 */
		open() {
			this.$panel.dialog.open(this);
		},
		/**
		 * When the overlay is open and fully usable
		 * the ready event is fired and forwarded here
		 * @public
		 */
		ready() {
			this.$panel.dialog.emit("ready");
		},
		/**
		 * This event is triggered when the submit button is clicked,
		 * or the form is submitted. It can also be called manually.
		 * @public
		 */
		submit() {
			this.$panel.dialog.submit(this.$panel.value);
		},
		/**
		 * Shows the success notification bar in the dialog with the given message
		 * @public
		 * @param {String|Object} message
		 */
		success(success) {
			this.$panel.dialog.success(success);
		}
	}
};
