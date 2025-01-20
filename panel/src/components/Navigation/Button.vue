<template>
	<component
		:is="component"
		v-bind="attrs"
		:class="['k-button', $attrs.class]"
		:data-has-icon="Boolean(icon)"
		:data-has-text="Boolean(text || $slots.default)"
		:style="$attrs.style"
		@click="onClick"
	>
		<span v-if="icon" class="k-button-icon">
			<k-icon :type="icon" />
		</span>
		<span v-if="text || $slots.default" class="k-button-text">
			<!--
				@slot The Button text. You can also use the `text` prop. Leave empty for icon buttons.
			-->
			<slot>
				{{ text }}
			</slot>
		</span>
		<span v-if="dropdown && (text || $slots.default)" class="k-button-arrow">
			<k-icon type="angle-dropdown" />
		</span>
		<span
			v-if="badge"
			class="k-button-badge"
			:data-theme="badge.theme ?? theme"
		>
			{{ badge.text }}
		</span>
	</component>
</template>

<script>
import { props as LinkProps } from "@/components/Navigation/Link.vue";

export const props = {
	mixins: [LinkProps],
	props: {
		/**
		 * Sets autofocus on button (when supported by element)
		 */
		autofocus: Boolean,
		/**
		 * Display a (colored) badge on the top-right of the button
		 * @value { text, theme }
		 * @example { text: 5, theme: "positive" }
		 * @since 5.0.0
		 */
		badge: Object,
		/**
		 * Pass instead of a link URL to be triggered on clicking the button
		 */
		click: {
			type: Function,
			default: () => {}
		},
		/**
		 * Sets the `aria-current` attribute.
		 * Especially useful in connection with the `link` attribute.
		 */
		current: [String, Boolean],
		/**
		 * Name/path of a dialog to open on click
		 */
		dialog: String,
		/**
		 * Name/path of a drawer to open on click
		 */
		drawer: String,
		/**
		 * Whether the button opens a dropdown
		 */
		dropdown: Boolean,
		/**
		 * Force which HTML element to use
		 */
		element: String,
		/**
		 * Adds an icon to the button.
		 */
		icon: String,
		/**
		 * A unique id for the HTML element
		 */
		id: [String, Number],
		/**
		 * If the link attribute is set, the button will be represented
		 * as a proper `a` tag with `link`'s value as `href` attribute.
		 */
		link: String,
		/**
		 * A responsive button will hide the button text on smaller screens
		 * automatically and only keep the icon. An icon must be set in this case.
		 * If set to `text`, the icon will be hidden instead.
		 */
		responsive: [Boolean, String],
		/**
		 * `role` attribute for the button
		 */
		role: String,
		/**
		 * Sets the `aria-selected` attribute.
		 */
		selected: [String, Boolean],
		/**
		 * Specific sizes for button styling
		 * @since 4.0.0
		 * @values "xs", "sm"
		 */
		size: String,
		/**
		 * The button text
		 */
		text: [String, Number],
		/**
		 * With the theme you can control the general design of the button.
		 */
		theme: String,
		/**
		 * The type attribute sets the button type like in HTML.
		 * @values "button", "submit", "reset"
		 */
		type: {
			type: String,
			default: "button"
		},
		/**
		 * Styling variants for the button
		 * @since 4.0.0
		 * @values "filled", "dimmed"
		 */
		variant: String
	}
};

/**
 * @example <k-button icon="check">Save</k-button>
 * @example <k-button icon="check" size="sm" variant="filled">Save</k-button>
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["click"],
	computed: {
		attrs() {
			// Shared
			const attrs = {
				"aria-current": this.current,
				"aria-disabled": this.disabled,
				"aria-label": this.text ?? this.title,
				"aria-selected": this.selected,
				"data-responsive": this.responsive,
				"data-size": this.size,
				"data-theme": this.theme,
				"data-variant": this.variant,
				id: this.id,
				tabindex: this.tabindex,
				title: this.title
			};

			if (this.component === "k-link") {
				// For `<a>`/`<k-link>` element:
				attrs["disabled"] = this.disabled;
				attrs["download"] = this.download;
				attrs["to"] = this.link;
				attrs["rel"] = this.rel;
				attrs["target"] = this.target;
			} else if (this.component === "button") {
				// For `<button>` element:
				attrs["autofocus"] = this.autofocus;
				attrs["role"] = this.role;
				attrs["type"] = this.type;
			}

			if (this.dropdown) {
				// For `<summary>` element/dropdowns:
				attrs["aria-haspopup"] = "menu";
				attrs["data-dropdown"] = this.dropdown;
			}

			return attrs;
		},
		component() {
			if (this.element) {
				return this.element;
			}

			if (this.link) {
				return "k-link";
			}

			return "button";
		}
	},
	methods: {
		/**
		 * Focus the button
		 * @public
		 */
		focus() {
			this.$el.focus?.();
		},
		onClick(e) {
			if (this.disabled) {
				e.preventDefault();
				return false;
			}

			if (this.dialog) {
				return this.$dialog(this.dialog);
			}

			if (this.drawer) {
				return this.$drawer(this.drawer);
			}

			this.click?.(e);

			/**
			 * The button has been clicked
			 * @property {PointerEvent} event the native click event
			 */
			this.$emit("click", e);
		}
	}
};
</script>

<style>
:root {
	--button-align: center;
	--button-height: var(--height-md);
	--button-width: auto;
	--button-color-back: none;
	--button-color-text: currentColor;
	--button-color-icon: currentColor;
	--button-padding: var(--spacing-2);
	--button-rounded: var(--spacing-1);
	--button-text-display: block;
	--button-icon-display: block;
	--button-filled-color-back: light-dark(
		var(--color-gray-300),
		var(--color-gray-950)
	);
}

.k-button {
	position: relative;
	display: inline-flex;
	align-items: center;
	justify-content: var(--button-align);
	gap: 0.5rem;
	padding-inline: var(--button-padding);
	white-space: nowrap;
	line-height: 1;
	border-radius: var(--button-rounded);
	background: var(--button-color-back);
	height: var(--button-height);
	width: var(--button-width);
	color: var(--button-color-text);
	font-variant-numeric: tabular-nums;
	text-align: var(--button-align);
	flex-shrink: 0;
}

.k-button-icon {
	--icon-color: var(--button-color-icon);
	flex-shrink: 0;
	display: var(--button-icon-display);
}

.k-button-text {
	text-overflow: ellipsis;
	overflow-x: clip;
	display: var(--button-text-display);
	min-width: 0;
}

/** Themed Buttons **/
.k-button:where([data-theme]) {
	--button-color-icon: var(--theme-color-icon);
	--button-color-text: var(--theme-color-text);
}
.k-button:where([data-theme$="-icon"]) {
	--button-color-text: currentColor;
}

/** Dimmed Buttons **/
.k-button:where([data-variant="dimmed"]) {
	--button-color-icon: var(--color-text);
	--button-color-text: var(--color-text-dimmed);
}
.k-button:where([data-variant="dimmed"]):not([aria-disabled="true"]):is(
		:hover,
		[aria-current="true"]
	)
	.k-button-text {
	filter: light-dark(brightness(75%), brightness(125%));
}
.k-button:where([data-variant="dimmed"][data-theme]) {
	--button-color-icon: var(--theme-color-icon);
	--button-color-text: var(--theme-color-text-dimmed);
}
.k-button:where([data-variant="dimmed"][data-theme$="-icon"]) {
	--button-color-text: var(--color-text-dimmed);
}

/** Filled Buttons **/
.k-button:where([data-variant="filled"]) {
	--button-color-back: var(--button-filled-color-back);
}
.k-button:where([data-variant="filled"]):not([aria-disabled="true"]):hover {
	filter: brightness(97%);
}

.k-button:where([data-variant="filled"][data-theme]) {
	--button-color-icon: var(--theme-color-icon-highlight);
	--button-color-back: var(--theme-color-back);
	--button-color-text: var(--theme-color-text-highlight);
}
.k-button:where([data-theme$="-icon"][data-variant="filled"]) {
	--button-color-icon: var(--theme-color-icon);
	--button-color-back: var(--button-filled-color-back);
	--button-color-text: currentColor;
}

/** Icon Buttons **/
.k-button:not([data-has-text="true"]) {
	--button-padding: 0;
	aspect-ratio: 1/1;
}

/** Responsive buttons **/
@container (max-width: 30rem) {
	.k-button[data-responsive="true"][data-has-icon="true"] {
		--button-padding: 0;
		aspect-ratio: 1/1;
		--button-text-display: none;
	}
	.k-button[data-responsive="text"][data-has-text="true"] {
		--button-icon-display: none;
	}
	.k-button[data-responsive="true"][data-has-icon="true"] .k-button-arrow {
		display: none;
	}
}

/** Inactive buttons **/
.k-button:not(button, a, summary, label, .k-link) {
	pointer-events: none;
}

/** Sizes **/
.k-button:where([data-size="xs"]) {
	--button-height: var(--height-xs);
	--button-padding: 0.325rem;
}
.k-button:where([data-size="sm"]) {
	--button-height: var(--height-sm);
	--button-padding: 0.5rem;
}
.k-button:where([data-size="lg"]) {
	--button-height: var(--height-lg);
}

/** Dropdown arrow **/
.k-button-arrow {
	width: max-content;
	margin-inline-start: -0.25rem;
	margin-inline-end: -0.125rem;
}

/** Badge **/
.k-button-badge {
	position: absolute;
	top: 0;
	inset-inline-end: 0;
	transform: translate(40%, -20%);
	min-width: 1em;
	min-height: 1em;
	font-variant-numeric: tabular-nums;
	line-height: 1.5;
	padding: 0 var(--spacing-1);
	border-radius: 1em;
	text-align: center;
	font-size: 0.6rem;
	box-shadow: var(--shadow-md);
	background: var(--theme-color-back);
	border: 1px solid light-dark(var(--theme-color-500), var(--color-black));
	color: var(--theme-color-text-highlight);
	z-index: 1;
}

/** Disabled button **/
.k-button:where([aria-disabled="true"]) {
	cursor: not-allowed;
}
.k-button:where([aria-disabled="true"]) > * {
	opacity: var(--opacity-disabled);
}
</style>
