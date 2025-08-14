<template>
	<dialog
		v-if="isOpen"
		ref="dropdown"
		:data-align-x="align.x"
		:data-align-y="align.y"
		:data-theme="theme"
		:style="{
			top: position.y + 'px',
			left: position.x + 'px'
		}"
		class="k-dropdown k-dropdown-content"
		@close="onClose"
		@click="onClick"
	>
		<k-navigate ref="navigate" :disabled="navigate === false" align="y">
			<!-- @slot Content of the dropdown which overrides passed `options` prop -->
			<slot v-bind="{ items }">
				<template v-for="(option, index) in items">
					<hr v-if="option === '-'" :key="'separator-' + index" />
					<slot
						v-else-if="option.when ?? true"
						name="item"
						v-bind="{ item: option, index }"
					>
						<k-dropdown-item
							:key="'item-' + index"
							v-bind="option"
							@click="onOptionClick(option)"
						>
							{{ option.label ?? option.text }}
						</k-dropdown-item>
					</slot>
				</template>
			</slot>
		</k-navigate>
	</dialog>
</template>

<script>
let OpenDropdown = null;

/**
 * Dropdowns are constructed with two elements: `<k-dropdown>` holds any content shown when opening the dropdown: any number of `<k-dropdown-item>` elements or any other HTML; typically a `<k-button>` then is used to call the `toggle()` method on `<k-dropdown>`.
 */
export default {
	props: {
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
			align: {
				x: this.alignX,
				y: this.alignY
			},
			position: {
				x: 0,
				y: 0
			},
			isOpen: false,
			items: [],
			opener: null
		};
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

			// click handler is a callback function
			// which is executed
			if (typeof option.click === "function") {
				return option.click.call(this);
			}

			// click handler is an action string
			// which is emitted to the parent
			if (typeof option.click === "string") {
				return this.$emit("action", option.click);
			}

			// click handler is an object with a name and payload
			// and optional flag to also emit globally
			if (option.click) {
				if (option.click.name) {
					this.$emit(option.click.name, option.click.payload);
				}

				if (option.click.global) {
					this.$events.emit(option.click.global, option.click.payload);
				}
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
			// reset to the alignment defaults
			// before running position calculation
			this.align = {
				x: this.alignX ?? this.align,
				y: this.alignY
			};

			if (this.align.x === "right") {
				this.align.x = "end";
			} else if (this.align.x === "left") {
				this.align.x = "start";
			}

			// flip x align for RTL languages
			if (this.$panel.direction === "rtl") {
				if (this.align.x === "start") {
					this.align.x = "end";
				} else if (this.align.x === "end") {
					this.align.x = "start";
				}
			}

			// drill down to the element of a Vue component
			if (this.opener.$el) {
				this.opener = this.opener.$el;
			}

			// get the dimensions of the opening button
			const opener = this.opener.getBoundingClientRect();

			// set the default position
			// and take scroll position into consideration
			this.position.x = opener.left + window.scrollX + opener.width;
			this.position.y = opener.top + window.scrollY + opener.height;

			// open the modal after the default positioning has been applied
			if (this.$el.open !== true) {
				this.$el.showModal();
			}

			// as we just set style.top, wait one tick before measuring dropdownRect
			await this.$nextTick();

			// get the dimensions of the open dropdown
			const rect = this.$el.getBoundingClientRect();
			const safeSpace = 10;

			// Horizontal: check if dropdown is outside of viewport
			// and adapt alignment if necessary
			if (this.align.x === "end") {
				if (opener.left - rect.width < safeSpace) {
					this.align.x = "start";
				}
			} else if (
				opener.left + rect.width > window.innerWidth - safeSpace &&
				rect.width + safeSpace < rect.left
			) {
				this.align.x = "end";
			}

			if (this.align.x === "start") {
				this.position.x = this.position.x - opener.width;
			}

			// Vertical: check if dropdown is outside of viewport
			// and adapt alignment if necessary
			if (this.align.y === "top") {
				if (rect.height + safeSpace > rect.top) {
					this.align.y = "bottom";
				}
			} else if (
				opener.top + rect.height > window.innerHeight - safeSpace &&
				rect.height + safeSpace < rect.top
			) {
				this.align.y = "top";
			}

			if (this.align.y === "top") {
				this.position.y = this.position.y - opener.height;
			}
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
	--dropdown-color-bg: var(--color-gray-950);
	--dropdown-color-current: var(--color-blue-500);
	--dropdown-color-hr: var(--color-gray-850);
	--dropdown-color-text: var(--color-white);
	--dropdown-padding: var(--spacing-2);
	--dropdown-rounded: var(--rounded);
	--dropdown-shadow: var(--shadow-xl);
}

.k-dropdown {
	--dropdown-x: 0;
	--dropdown-y: 0;
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
	transform: translate(var(--dropdown-x), var(--dropdown-y));
}
.k-dropdown::backdrop {
	background: none;
}

.k-dropdown[data-align-x="end"] {
	--dropdown-x: -100%;
}
.k-dropdown[data-align-x="center"] {
	--dropdown-x: -50%;
}
.k-dropdown[data-align-y="top"] {
	--dropdown-y: -100%;
}

.k-dropdown hr {
	margin: 0.5rem 0;
	height: 1px;
	background: var(--dropdown-color-hr);
}

.k-dropdown[data-theme="light"] {
	--dropdown-color-bg: var(--color-white);
	--dropdown-color-current: var(--color-blue-800);
	--dropdown-color-hr: var(--color-gray-250);
	--dropdown-color-text: var(--color-black);
}
</style>
