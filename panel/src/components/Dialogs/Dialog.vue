<template>
	<k-overlay
		ref="overlay"
		:autofocus="autofocus"
		:centered="true"
		@close="onOverlayClose"
		@ready="$emit('ready')"
	>
		<k-dialog-box ref="dialog" :size="size" :class="$vnode.data.staticClass">
			<k-dialog-form @submit="submit">
				<k-dialog-notification
					v-if="notification"
					v-bind="notification"
					@close="notification = null"
				/>
				<k-dialog-body>
					<slot />
				</k-dialog-body>
				<slot name="footer">
					<k-dialog-buttons
						:cancel-button="cancelButton"
						:disabled="disabled"
						:icon="icon"
						:submit-button="submitButton"
						:theme="theme"
						@cancel="cancel"
						@submit="submit"
					/>
				</slot>
			</k-dialog-form>
		</k-dialog-box>
	</k-overlay>
</template>

<script>
import { props as Box } from "./Elements/Box.vue";
import { props as Buttons } from "./Elements/Buttons.vue";

export const props = {
	mixins: [Box, Buttons],
	props: {
		/**
		 * The first focusable element is focused by default,
		 * but this behaviour can be switched off.
		 */
		autofocus: {
			type: Boolean,
			default: true
		},
		/**
		 * Dialogs are only openend on demand with the `open()` method. If you need a dialog that's visible on creation, you can set the `visible` prop
		 */
		visible: Boolean
	}
};

/**
 * Modal dialogs are used in Kirby's Panel in many places for quick actions like adding new pages, changing titles, etc. that don't necessarily need a full new view. You can create your own modals for your fields and other plugins or reuse our existing modals to invoke typical Panel actions.
 */
export default {
	mixins: [props],
	data() {
		return {
			notification: null
		};
	},
	created() {
		this.$events.$on("keydown.esc", this.close, false);
	},
	destroyed() {
		this.$events.$off("keydown.esc", this.close, false);
	},
	mounted() {
		if (this.visible) {
			this.$nextTick(this.open);
		}
	},
	methods: {
		/**
		 * Reacts to the overlay being closed
		 * and cleans up the dialog events
		 * @private
		 */
		onOverlayClose() {
			this.notification = null;
			/**
			 * This event is triggered when the dialog is being closed.
			 * This happens independently from the cancel event.
			 * @event close
			 */
			this.$emit("close");
			this.$events.$off("keydown.esc", this.close);
			this.$store.dispatch("dialog", false);
		},
		/**
		 * Opens the dialog and triggers the `@open` event
		 * @public
		 */
		open() {
			// when dialogs are used in the old-fashioned way
			// by adding their component to a template and calling
			// open on the component manually, the dialog state
			// is set to true. In comparison, this.$dialog fills
			// the dialog state after a successfull request and
			// the fiber dialog component is injected on store change
			// automatically.
			if (!this.$store.state.dialog) {
				this.$store.dispatch("dialog", true);
			}

			this.notification = null;
			this.$refs.overlay.open();
			/**
			 * This event is triggered as soon as the dialog opens.
			 * @event open
			 */
			this.$emit("open");
			this.$events.$on("keydown.esc", this.close);
		},
		/**
		 * Triggers the `@close` event and closes the dialog.
		 * @public
		 */
		close() {
			if (this.$refs.overlay) {
				this.$refs.overlay.close();
			}
		},
		/**
		 * Triggers the `@cancel` event and closes the dialog.
		 * @public
		 */
		cancel() {
			/**
			 * This event is triggered whenever the cancel button or
			 * the backdrop is clicked.
			 * @event cancel
			 */
			this.$emit("cancel");
			this.close();
		},
		focus() {
			if (this.$refs.dialog?.querySelector) {
				const btn = this.$refs.dialog.querySelector(".k-dialog-button-cancel");

				if (typeof btn?.focus === "function") {
					btn.focus();
				}
			}
		},
		/**
		 * Shows the error notification bar in the dialog with the given message
		 * @public
		 * @param {string} message
		 */
		error(message) {
			this.notification = {
				message: message,
				type: "error"
			};
		},
		submit() {
			/**
			 * This event is triggered when the submit button is clicked.
			 * @event submit
			 */
			this.$emit("submit");
		},
		/**
		 * Shows the success notification bar in the dialog with the given message
		 * @public
		 * @param {string} message
		 */
		success(message) {
			this.notification = {
				message: message,
				type: "success"
			};
		}
	}
};
</script>
