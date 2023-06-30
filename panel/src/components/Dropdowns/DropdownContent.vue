<template>
	<dialog
		ref="dropdown"
		v-if="isOpen"
		:data-align="align"
		:data-dropup="dropup"
		:data-theme="theme"
		class="k-dropdown-content"
		@close="onClose"
		@click="onClick"
	>
		<!-- @slot Content of the dropdown -->
		<k-navigate ref="navigate" :disabled="navigate === false" axis="y">
			<slot>
				<template v-for="(option, index) in items">
					<hr v-if="option === '-'" :key="_uid + '-item-' + index" />
					<k-dropdown-item
						v-else
						:key="_uid + '-item-' + index"
						v-bind="option"
						@click="onOptionClick(option)"
					>
						{{ option.text }}
					</k-dropdown-item>
				</template>
			</slot>
		</k-navigate>
	</dialog>
</template>

<script>
let OpenDropdown = null;

/**
 * See `<k-dropdown>` for how to use these components together.
 * @internal
 */
export default {
	props: {
		/**
		 * Alignment of the dropdown items
		 * @values left, right
		 */
		align: {
			type: String,
			default: "left"
		},
		navigate: {
			default: true,
			type: Boolean
		},
		options: [Array, Function, String],
		/**
		 * Visual theme of the dropdown
		 * @values dark, light
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
			dropup: false,
			isOpen: false,
			items: []
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
		onClick(event) {
			// close the dialog if the backdrop is being clicked
			if (event.target === this.$el) {
				this.close();
			}
		},
		onClose() {
			this.resetPosition();
			this.isOpen = OpenDropdown = false;
			this.$emit("close");
		},
		onOpen(opener) {
			this.isOpen = true;

			// store a global reference to the dropdown
			OpenDropdown = this;

			// wait until the dropdown is rendered
			this.$nextTick(() => {
				if (this.$el && opener) {
					this.position(opener);
					this.$emit("open");
				}
			});
		},
		onOptionClick(option) {
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
			if (OpenDropdown && OpenDropdown !== this) {
				// close the current dropdown
				OpenDropdown.close();
			}

			// find the opening element
			opener =
				opener ??
				window.event?.target.closest("button") ??
				window.event?.target;

			// load all options and open the dropdown as
			// soon as they are loaded
			this.fetchOptions((items) => {
				this.items = items;
				this.onOpen(opener);
			});
		},
		position(opener) {
			// reset the dropup state before position calculation
			this.dropup = false;

			// get the dimensions of the opening button
			const openerRect = opener.getBoundingClientRect();

			// set the top position and take scroll position into consideration
			this.$el.style.top =
				openerRect.top + window.scrollY + openerRect.height + "px";

			// set the left position based on the alignment
			const offsetX =
				this.align === "end" || this.align === "right" ? openerRect.width : 0;
			this.$el.style.left = openerRect.left + window.scrollX + offsetX + "px";

			// open the modal after the correct positioning has been applied
			if (this.$el.open !== true) {
				this.$el.showModal();
			}

			// as we just set style.top, wait one tick before measuring dropdownRect
			this.$nextTick(() => {
				// get the dimensions of the open dropdown
				const dropdownRect = this.$el.getBoundingClientRect();
				const safeSpaceHeight = 10;

				// activates the dropup if the dropdown content overflows
				// to the bottom of the screen but only if there is
				// enough space top of screen
				if (
					dropdownRect.top + dropdownRect.height >
						window.innerHeight - safeSpaceHeight &&
					dropdownRect.height + safeSpaceHeight * 2 < dropdownRect.top
				) {
					this.$el.style.top =
						parseInt(this.$el.style.top) - openerRect.height + "px";
					this.dropup = true;
				}
			});
		},
		resetPosition() {
			this.$el.style.top = 0;
			this.$el.style.left = 0;
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
	--dropdown-x: 0;
	--dropdown-y: 0;
	position: absolute;
	inset-block-start: 0;
	inset-inline-start: 0;
	width: max-content;
	padding: var(--dropdown-padding);
	background: var(--dropdown-color-bg);
	border-radius: var(--dropdown-rounded);
	color: var(--dropdown-color-text);
	box-shadow: var(--dropdown-shadow);
	text-align: start;
	transform: translate(var(--dropdown-x), var(--dropdown-y));
}
.k-dropdown-content::backdrop {
	background: none;
}

.k-dropdown-content[data-align="right"],
.k-dropdown-content[data-align="end"] {
	--dropdown-x: -100%;
}

.k-dropdown-content[data-dropup="true"] {
	--dropdown-y: -100%;
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
