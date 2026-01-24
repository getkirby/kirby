import { props as Buttons } from "@/components/Dialogs/Elements/Buttons.vue";

/**
 * The Dialog mixin is intended for all components
 * that extend <k-dialog> It forwards the methods to
 * the <k-dialog> ref. Extending <k-dialog> directly
 * can lead to breaking methods when the methods are not
 * wired correctly to the right elements and refs.
 */
export default {
	mixins: [Buttons],
	props: {
		/**
		 * Disables native browser form validation
		 * @since 5.3.0
		 */
		novalidate: {
			default: false,
			type: Boolean
		},
		/**
		 * Width of the dialog
		 * @values "small", "default", "medium", "large", "huge"
		 */
		size: {
			default: "default",
			type: String
		},
		visible: {
			default: false,
			type: Boolean
		}
	},
	emits: ["cancel", "close", "input", "submit", "success"],
	methods: {
		/**
		 * Triggers the `@cancel` event and closes the dialog.
		 * @public
		 */
		cancel() {
			this.$emit("cancel");
		},
		/**
		 * Triggers the `@close` event and closes the dialog.
		 * @public
		 */
		close() {
			this.$emit("close");
		},
		/**
		 * Shows the error notification bar in the dialog with the given message
		 * @param {String} error
		 */
		error(error) {
			this.$panel.notification.error(error);
		},
		/**
		 * Sets the focus on the first usable input
		 * or a given input by name
		 * @public
		 * @param {String} input
		 */
		focus(input) {
			this.$panel.dialog.focus(input);
		},
		/**
		 * Updates the dialog values
		 * @public
		 * @param {Object} value new values
		 */
		input(value) {
			this.$emit("input", value);
		},
		/**
		 * Opens the dialog and triggers the `@open` event.
		 * Use ready to fire events that should be run as
		 * soon as the dialog is open
		 * @public
		 */
		open() {
			this.$panel.dialog.open(this);
		},
		submit() {
			/**
			 * The submit button is clicked or the form is submitted.
			 */
			this.$emit("submit", this.value);
		},
		/**
		 * Shows the success notification bar in the dialog with the given message
		 * @param {String|Object} message
		 */
		success(success) {
			this.$emit("success", success);
		}
	}
};
