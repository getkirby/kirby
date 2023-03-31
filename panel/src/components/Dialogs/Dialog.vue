<template>
	<k-overlay
		ref="overlay"
		:autofocus="autofocus"
		:centered="centered"
		:dimmed="dimmed"
		:loading="loading"
		:visible="visible"
		class="k-dialog-overlay"
		@cancel="cancel"
		@ready="ready"
	>
		<k-dialog-box :size="size" :class="$vnode.data.staticClass">
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
					<k-dialog-footer v-if="cancelButton || submitButton">
						<k-dialog-buttons
							:cancel-button="cancelButton"
							:disabled="disabled"
							:icon="icon"
							:submit-button="submitButton"
							:theme="theme"
							@cancel="cancel"
							@submit="submit"
						/>
					</k-dialog-footer>
				</slot>
			</k-dialog-form>
		</k-dialog-box>
	</k-overlay>
</template>

<script>
import { props as Box } from "./Elements/Box.vue";
import { props as Buttons } from "./Elements/Buttons.vue";
import { props as Overlay } from "@/components/Layout/Overlay.vue";

export const props = {
	mixins: [Overlay, Box, Buttons],
	props: {
		/**
		 * Dialogs are centered by default.
		 * The overlay sets the default to false
		 * here so we need to overwrite it.
		 */
		centered: {
			default: true
		}
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
	methods: {
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
		/**
		 * Triggers the `@close` event and closes the dialog.
		 * @public
		 */
		close() {
			this.notification = null;

			/**
			 * This event is triggered when the dialog is being closed.
			 * This happens independently from the cancel event.
			 * @event close
			 */
			this.$emit("close");
			this.$store.dispatch("dialog", false);

			/**
			 * close the overlay if it is still there
			 * in fiber dialogs the entire dialog compoengets destroyed
			 * and this step is not necessary
			 */
			this.$refs.overlay?.close();
		},
		/**
		 * Shows the error notification bar in the dialog with the given message
		 * @public
		 * @param {string} message
		 */
		error(message) {
			// resolve error objects
			if (message instanceof Error) {
				message = message.message;
			}

			this.notification = {
				message: message,
				type: "error"
			};
		},
		/**
		 * The overlay component has a built-in focus
		 * method that finds the best first element to
		 * focus on
		 */
		focus() {
			this.$refs.overlay.focus();
		},
		/**
		 * Opens the overlay and triggers the `@open` event
		 * Use the `ready` event to
		 * @public
		 */
		open() {
			// show the overlay
			this.$refs.overlay.open();

			/**
			 * This event is triggered as soon as the dialog is being opened.
			 * @event open
			 */
			this.$emit("open");
		},
		/**
		 * When the overlay is open and fully usable
		 * the ready event is fired and forwarded here
		 */
		ready() {
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

			// close any notifications if there's still an open one
			this.notification = null;

			/**
			 * Mark the dialog as ready to be used
			 * @event ready
			 */
			this.$emit("ready");
		},
		/**
		 * This event is triggered when the submit button is clicked,
		 * or the form is submitted. It can also be called manually.
		 * @public
		 */
		submit() {
			/**
			 * @event submit
			 */
			this.$emit("submit");
		},
		/**
		 * Shows the success notification bar in the dialog with the given message
		 * @public
		 * @param {string} message
		 */
		success(success) {
			// send a success message to the dialog
			// and keep the dialog open if a simple
			// string is passed to the method
			if (typeof success === "string") {
				this.notification = {
					message: message,
					type: "success"
				};

				// keep the dialog open
				return;
			}

			// send a global success notification
			if (success.message) {
				this.$store.dispatch("notification/success", success.message);
			}

			// dispatch store actions that might have been defined in
			// the success response
			if (success.dispatch) {
				Object.keys(success.dispatch).forEach((event) => {
					const payload = success.dispatch[event];
					this.$store.dispatch(
						event,
						Array.isArray(payload) === true ? [...payload] : payload
					);
				});
			}

			// send optional events to the event bus
			if (success.event) {
				// wrap events in an array
				success.event = Array.isArray(success.event)
					? success.event
					: [success.event];
				success.event.forEach((event) => {
					this.$events.$emit(event, success);
				});
			}

			// emit a general success event unless it is
			// explicitely blocked
			if (success?.emit !== false) {
				this.$emit("success");
			}

			// redirect (route is deprecated)
			if (success.redirect || success.route) {
				return this.$go(success.redirect || success.route);
			}

			// reload the current view
			this.$reload(success.reload || {});
		}
	}
};
</script>
