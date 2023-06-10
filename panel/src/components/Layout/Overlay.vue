<template>
	<portal v-if="isOpen" :to="type">
		<div
			ref="overlay"
			:data-centered="loading || centered"
			:data-dimmed="dimmed"
			:data-loading="loading"
			:dir="$panel.direction"
			:class="'k-' + type + '-overlay'"
			class="k-overlay"
			@click="click"
		>
			<k-icon v-if="loading" type="loader" class="k-overlay-loader" />
			<slot v-else :close="close" :is-open="isOpen" />
		</div>
	</portal>
</template>

<script>
export const props = {
	props: {
		autofocus: {
			default: true,
			type: Boolean
		},
		centered: {
			default: false,
			type: Boolean
		},
		dimmed: {
			default: true,
			type: Boolean
		},
		loading: {
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
	data() {
		return {
			isOpen: false,
			scrollTop: 0
		};
	},
	watch: {
		visible: {
			handler(visible) {
				visible === true ? this.open() : this.close();
			},
			immediate: true
		}
	},
	mounted() {
		if (this.visible) {
			this.open();
		}
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
		 * Check for clicks on the backdrop
		 */
		click(event) {
			// compare the event target with the overlay element
			if (event.target === this.$refs.overlay) {
				this.cancel();
			}
		},
		/**
		 * Closes the overlay, removes the escape key listener
		 * and restores the scroll position in the panel view
		 */
		close() {
			// it makes it run once
			if (this.isOpen === false) {
				return;
			}

			this.isOpen = false;
			this.$emit("close");
			this.restoreScrollPosition();

			// unbind events
			this.$events.$off("keydown.esc", this.cancel);
		},
		focus() {
			this.$helper.focus(this.$refs.overlay);
		},
		/**
		 * Alias for close. This is needed to simplify
		 * hiding dialogs and drawers.
		 */
		hide() {
			this.close();
		},
		open() {
			// it makes it run once
			if (this.isOpen === true) {
				return;
			}

			this.storeScrollPosition();
			this.isOpen = true;
			this.$emit("open");

			// listen for the escape key to
			// close the overlay
			this.$events.$on("keydown.esc", this.cancel);

			// wait for the next rendering round
			// otherwise the portal won't be ready
			setTimeout(() => {
				// autofocus
				if (this.autofocus === true) {
					this.focus();
				}

				this.$emit("ready");
			});
		},
		restoreScrollPosition() {
			const view = document.querySelector(".k-panel-view");

			if (view?.scrollTop) {
				view.scrollTop = this.scrollTop;
			}
		},
		/**
		 * Alias for open. This is needed to simplify
		 * showing dialogs and drawers.
		 */
		show() {
			this.open();
		},
		storeScrollPosition() {
			const view = document.querySelector(".k-panel-view");

			if (view?.scrollTop) {
				this.scrollTop = view.scrollTop;
			} else {
				this.scrollTop = 0;
			}
		}
	}
};
</script>

<style>
:root {
	--overlay-color-back: var(--color-backdrop);
}

.k-overlay {
	position: fixed;
	inset: 0;
	width: 100%;
	height: 100vh;
	height: 100dvh;
	z-index: var(--z-dialog);
	transform: translate3d(0, 0, 0);
}
.k-overlay[data-centered="true"] {
	display: grid;
	place-items: center;
}
.k-overlay[data-dimmed="true"] {
	background: var(--overlay-color-back);
}
.k-overlay-loader {
	color: var(--color-white);
}

/* Scroll lock */
:where(body):has(.k-overlay) {
	overflow: clip;
}
</style>
