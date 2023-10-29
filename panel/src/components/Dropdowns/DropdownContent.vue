<template>
	<dialog
		v-if="isOpen"
		ref="dropdown"
		:data-theme="theme"
		:style="{
			top: position.y + 'px',
			left: position.x + 'px'
		}"
		class="k-dropdown-content"
		@close="onClose"
		@click="onClick"
	>
		<k-navigate ref="navigate" :disabled="navigate === false" axis="y">
			<!-- @slot Content of the dropdown which overrides passed `options` prop -->
			<slot>
				<template v-for="(option, index) in items">
					<hr v-if="option === '-'" :key="_uid + '-item-' + index" />
					<k-dropdown-item
						v-else-if="option.when ?? true"
						:key="_uid + '-item-' + index"
						v-bind="option"
						@click="onOptionClick(option)"
					>
						{{ option.label ?? option.text }}
					</k-dropdown-item>
				</template>
			</slot>
		</k-navigate>
	</dialog>
</template>

<script>
import { position } from "@/helpers/dropdown.js";

let OpenDropdown = null;

/**
 * Dropdowns are constructed with two elements: `<k-dropdown-content>` holds any content shown when opening the dropdown: any number of `<k-dropdown-item>` elements or any other HTML; typically a `<k-button>` then is used to call the `toggle()` method on `<k-dropdown-content>`.
 */
export default {
	props: {
		/**
		 * @deprecated 4.0.0 Use `align-x` instead
		 */
		align: {
			type: String
		},
		/**
		 * Default horizontal alignment of the dropdown
		 * @since 4.0.0
		 * @values "start", "end", "center"
		 */
		alignX: {
			type: String,
			default: "start"
		},
		/**
		 * Default vertical alignment of the dropdown
		 * @since 4.0.0
		 * @values "top", "bottom"
		 */
		alignY: {
			type: String,
			default: "bottom"
		},
		/**
		 * @since 4.0.0
		 */
		disabled: {
			type: Boolean,
			default: false
		},
		/**
		 * @since 4.0.0
		 */
		navigate: {
			default: true,
			type: Boolean
		},
		options: [Array, Function, String],
		/**
		 * Visual theme of the dropdown
		 * @values "dark", "light"
		 */
		theme: {
			type: String,
			default: "dark"
		}
	},
	emits: [
		"action",
		/**
		 * When the dropdown content is closed
		 * @event close
		 */
		"close",
		/**
		 * When the dropdown content is opened
		 * @event open
		 */
		"open"
	],
	data() {
		return {
			position: { x: 0, y: 0 },
			isOpen: false,
			items: [],
			opener: null
		};
	},
	created() {
		if (this.align) {
			window.panel.deprecated(
				"<k-dropdown-content>: `align` prop will be removed in a future version. Use the `alignX` prop instead."
			);
		}
	},
	methods: {
		/**
		 * Closes the dropdown
		 * @public
		 */
		close() {
			this.$refs.dropdown?.close();
		},
		async fetchOptions(ready) {
			if (!this.options) {
				return ready(this.items);
			}

			// resolve a dropdown URL
			if (typeof this.options === "string") {
				return this.$dropdown(this.options)(ready);
			}

			// resolve a callback function
			if (typeof this.options === "function") {
				return this.options(ready);
			}

			// resolve options from a simple array
			if (Array.isArray(this.options)) {
				return ready(this.options);
			}
		},
		focus(n = 0) {
			this.$refs.navigate.focus(n);
		},
		onClick() {
			this.close();
		},
		onClose() {
			this.resetPosition();
			this.isOpen = OpenDropdown = false;
			this.$emit("close");
			window.removeEventListener("resize", this.setPosition);
		},
		async onOpen() {
			this.isOpen = true;

			// remember the current scroll position
			const scrollTop = window.scrollY;

			// store a global reference to the dropdown
			OpenDropdown = this;

			// wait until the dropdown is rendered
			await this.$nextTick();

			if (this.$el && this.opener) {
				window.addEventListener("resize", this.setPosition);
				await this.setPosition();
				// restore the scroll position
				window.scrollTo(0, scrollTop);
				this.$emit("open");
			}
		},
		onOptionClick(option) {
			this.close();

			if (typeof option.click === "function") {
				option.click.call(this);
			} else if (option.click) {
				this.$emit("action", option.click);
			}
		},
		/**
		 * Opens the dropdown
		 * @public
		 */
		open(opener) {
			if (this.disabled === true) {
				return false;
			}

			if (OpenDropdown && OpenDropdown !== this) {
				// close the current dropdown
				OpenDropdown.close();
			}

			// find the opening element
			this.opener =
				opener ??
				window.event?.target.closest("button") ??
				window.event?.target;

			// load all options and open the dropdown as
			// soon as they are loaded
			this.fetchOptions((items) => {
				this.items = items;
				this.onOpen();
			});
		},
		async setPosition() {
			// remember the current scroll position as it will be 0
			// after the modal is opened
			const scroll = {
				x: window.scrollX,
				y: window.scrollY
			};

			// open the modal after the default positioning has been applied
			if (this.$el.open !== true) {
				this.$el.showModal();
				await this.$nextTick();
			}

			this.position = position(
				this.opener,
				this.$el,
				this.alignX ?? this.align,
				this.alignY,
				scroll
			);
		},
		resetPosition() {
			this.position = { x: 0, y: 0 };
		},
		/**
		 * Toggles the open state of the dropdown
		 * @public
		 */
		toggle(opener) {
			this.isOpen ? this.close() : this.open(opener);
		}
	}
};
</script>

<style>
:root {
	--dropdown-color-bg: var(--color-black);
	--dropdown-color-text: var(--color-white);
	--dropdown-color-hr: rgba(255, 255, 255, 0.25);
	--dropdown-padding: var(--spacing-2);
	--dropdown-rounded: var(--rounded);
	--dropdown-shadow: var(--shadow-xl);
}

.k-dropdown-content {
	position: absolute;
	inset-block-start: 0;
	inset-inline-start: initial; /* reset this, so that `left` is authoritative */
	left: 0;
	width: max-content;
	padding: var(--dropdown-padding);
	background: var(--dropdown-color-bg);
	border-radius: var(--dropdown-rounded);
	color: var(--dropdown-color-text);
	box-shadow: var(--dropdown-shadow);
	text-align: start;
}
.k-dropdown-content::backdrop {
	background: none;
}

.k-dropdown-content hr {
	margin: 0.5rem 0;
	height: 1px;
	background: var(--dropdown-color-hr);
}

.k-dropdown-content[data-theme="light"] {
	--dropdown-color-bg: var(--color-white);
	--dropdown-color-text: var(--color-black);
	--dropdown-color-hr: rgba(0, 0, 0, 0.1);
}
</style>
