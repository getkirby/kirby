<template>
	<dialog
		ref="overlay"
		:data-type="type"
		class="k-overlay"
		@cancel="onCancel"
		@mousedown="onClick"
		@touchdown="onClick"
		@close="onClose"
	>
		<slot />
	</dialog>
</template>

<script>
/**
 *
 */
export const props = {
	props: {
		autofocus: {
			default: true,
			type: Boolean
		},
		nested: {
			default: false,
			type: Boolean
		},
		type: {
			default: "overlay",
			type: String
		},
		/**
		 * Overlays are only openend on demand with the `open()` method.
		 * If you need an overlay that's visible on creation, you can set the
		 * `visible` prop
		 */
		visible: {
			default: false,
			type: Boolean
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: true,
	emits: ["cancel", "close", "open"],
	watch: {
		visible(newValue, oldValue) {
			if (newValue === oldValue) {
				return;
			}

			this.toggle();
		}
	},
	mounted() {
		this.toggle();
	},
	methods: {
		/**
		 * The cancel event is fired when the backdrop is
		 * clicked or the ESC key is pressed
		 */
		cancel() {
			this.$emit("cancel");
			this.close();
		},
		/**
		 * Closes the overlay, removes the escape key listener
		 * and restores the scroll position in the panel view
		 */
		close() {
			// it makes it run once
			if (this.$refs.overlay.open === false) {
				return;
			}

			// fire the event without
			// actually closing the overlay
			if (this.nested) {
				return this.onClose();
			}

			this.$refs.overlay.close();
		},
		focus() {
			this.$helper.focus(this.$refs.overlay);
		},
		onCancel(event) {
			// don't close the overlay when the
			// escape key is pressed when this is
			// a nested overlay.
			if (this.nested) {
				event.preventDefault();
				this.cancel();
			}
		},
		/**
		 * Check for clicks on the backdrop
		 */
		onClick(event) {
			if (event.target.matches(".k-portal")) {
				this.cancel();
			}
		},
		onClose() {
			this.$emit("close");
		},
		open() {
			// it makes it run once
			if (this.$refs.overlay.open !== true) {
				this.$refs.overlay.showModal();
			}

			// wait for the next rendering round
			// otherwise the portal won't be ready
			setTimeout(() => {
				// autofocus
				if (this.autofocus === true) {
					this.focus();
				}

				this.$emit("open");
			});
		},
		toggle() {
			if (this.visible === true) {
				this.open();
			} else {
				this.close();
			}
		}
	}
};
</script>

<style>
:root {
	--overlay-color-back: rgba(0, 0, 0, 0.6);
	--overlay-color-back-dimmed: rgba(0, 0, 0, 0.2);
}

.k-overlay[open] {
	position: fixed;
	overscroll-behavior: contain;
	inset: 0;
	width: 100%;
	height: 100vh;
	height: 100dvh;
	background: none;
	z-index: var(--z-dialog);
	transform: translate3d(0, 0, 0);
	overflow: hidden;
}
.k-overlay[open]::backdrop {
	background: none;
}

.k-overlay[open] > .k-portal {
	position: fixed;
	inset: 0;
	background: var(--overlay-color-back);
	overflow: auto;
}

.k-overlay[open][data-type="dialog"] > .k-portal {
	display: inline-flex;
}

.k-overlay[open][data-type="dialog"] > .k-portal > * {
	margin: auto;
}

.k-overlay[open][data-type="drawer"] > .k-portal {
	--overlay-color-back: var(--overlay-color-back-dimmed);
	display: flex;
	align-items: stretch;
	justify-content: flex-end;
}

/* Scroll lock */
html[data-overlay="true"] {
	overflow: hidden;
}
html[data-overlay="true"] body {
	overflow: scroll;
}
</style>
