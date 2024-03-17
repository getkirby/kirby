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
			<k-icon type="angle-down" />
		</span>
	</component>
</template>

<script>
/**
 * @example <k-button icon="check">Save</k-button>
 * @example <k-button icon="check" size="sm" variant="filled">Save</k-button>
 */
export default {
	inheritAttrs: false,
	props: {
		/**
		 * Sets autofocus on button (when supported by element)
		 */
		autofocus: Boolean,
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
		 * A disabled button will have no pointer events and
		 * the opacity is be reduced.
		 */
		disabled: Boolean,
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
		 * `rel` attribute for when using with `link`
		 */
		rel: String,
		/**
		 * `role` attribute for when using with `link`
		 */
		role: String,
		/**
		 * Sets the `aria-selected` attribute.
		 */
		selected: [String, Boolean],
		/**
		 * Specific sizes for buttong styling
		 * @since 4.0.0
		 * @values "xs", "sm"
		 */
		size: String,
		/**
		 * In connection with the `link` attribute, you can also set the
		 * target of the link. This does not apply to regular buttons.
		 */
		target: String,
		/**
		 * Custom tabindex. Only use if you really know how to adjust the order properly.
		 */
		tabindex: String,
		/**
		 * The button text
		 */
		text: [String, Number],
		/**
		 * With the theme you can control the general design of the button.
		 */
		theme: String,
		/**
		 * The title attribute can be used to add additional text
		 * to the button, which is shown on mouseover.
		 * @since 4.0.0
		 */
		title: String,
		/**
		 * @deprecated 4.0.0 Use the `title` prop instead
		 */
		tooltip: String,
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
	},
	emits: ["click"],
	computed: {
		attrs() {
			// Shared
			const attrs = {
				"aria-current": this.current,
				"aria-disabled": this.disabled,
				"aria-selected": this.selected,
				"data-responsive": this.responsive,
				"data-size": this.size,
				"data-theme": this.theme,
				"data-variant": this.variant,
				id: this.id,
				tabindex: this.tabindex,
				/** @todo button.prop.tooltip.deprecated - adapt @ 5.0 */
				title: this.title ?? this.tooltip
			};

			if (this.component === "k-link") {
				// For `<a>`/`<k-link>` element:
				attrs["disabled"] = this.disabled;
				attrs["to"] = this.link;
				attrs["rel"] = this.rel;
				attrs["role"] = this.role;
				attrs["target"] = this.target;
			} else if (this.component === "button") {
				// For `<button>` element:
				attrs["autofocus"] = this.autofocus;
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
	mounted() {
		if (this.tooltip) {
			window.panel.deprecated(
				"<k-button>: the `tooltip` prop will be removed in a future version. Use the `title` prop instead."
			);
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
	overflow-x: clip;
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

/** Dimmed Buttons **/
.k-button:where([data-variant="dimmed"]) {
	--button-color-icon: var(--color-text);
	--button-color-dimmed-on: var(--color-text-dimmed);
	--button-color-dimmed-off: var(--color-text);
	--button-color-text: var(--button-color-dimmed-on);
}
.k-button:where([data-variant="dimmed"]):not([aria-disabled="true"]):is(
		:hover,
		[aria-current="true"]
	) {
	--button-color-text: var(--button-color-dimmed-off);
}
.k-button:where([data-theme][data-variant="dimmed"]) {
	--button-color-icon: var(--theme-color-icon);
	--button-color-dimmed-on: var(--theme-color-text-dimmed);
	--button-color-dimmed-off: var(--theme-color-text);
}

/** Filled Buttons **/
.k-button:where([data-variant="filled"]) {
	--button-color-back: var(--color-gray-300);
}
.k-button:where([data-variant="filled"]):not([aria-disabled="true"]):hover {
	filter: brightness(97%);
}
.k-panel[data-theme="dark"]
	.k-button:where([data-variant="filled"]):not([aria-disabled]):hover {
	filter: brightness(87%);
}

.k-button:where([data-theme][data-variant="filled"]) {
	--button-color-icon: var(--theme-color-700);
	--button-color-back: var(--theme-color-back);
	--button-color-text: var(--theme-color-text);
}

/** Icon Buttons **/
/** TODO: .k-button:not(:has(.k-button-text)) */
.k-button:not([data-has-text="true"]) {
	--button-padding: 0;
	aspect-ratio: 1/1;
}

/** Responsive buttons **/
@container (max-width: 30rem) {
	/** TODO: .k-button:is([data-responsive]:has(.k-button-icon)) */
	.k-button[data-responsive="true"][data-has-icon="true"] {
		--button-padding: 0;
		aspect-ratio: 1/1;
		--button-text-display: none;
	}
	.k-button[data-responsive="text"][data-has-text="true"] {
		--button-icon-display: none;
	}
	/** TODO: .k-button:is([data-responsive]:has(.k-button-icon)) .k-button-arrow */
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
	--icon-size: 14px;
	width: max-content;
	margin-inline-start: -0.125rem;
}

/** Disabled button **/
.k-button:where([aria-disabled="true"]) {
	cursor: not-allowed;
}
.k-button:where([aria-disabled="true"]) > * {
	opacity: var(--opacity-disabled);
}
</style>
