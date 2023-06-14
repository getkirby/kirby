<template>
	<component :is="component" v-bind="attrs" class="k-button" @click="onClick">
		<k-icon v-if="icon" :type="icon" />

		<span v-if="text || $slots.default" class="k-button-text">
			<template v-if="text">
				{{ text }}
			</template>
			<!-- @deprecated 4.0 Use `text` prop instead -->
			<!-- @todo button.slot.deprecated - remove @ 5.0 -->
			<template v-else>
				<slot />
			</template>
		</span>

		<span v-if="dropdown && text" class="k-button-arrow">
			<k-icon type="angle-down" />
		</span>
	</component>
</template>

<script>
import tab from "@/mixins/tab.js";

/**
 * @example <k-button icon="check">Save</k-button>
 */
export default {
	mixins: [tab],
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
		 * A disabled button will have no pointer events and
		 * the opacity is be reduced.
		 */
		disabled: Boolean,
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
		id: [String, Number],
		/**
		 * If the link attribute is set, the button will be represented
		 * as a proper `a` tag with `link`'s value as `href` attribute.
		 */
		link: String,
		/**
		 * A responsive button will hide the button text on smaller screens
		 * automatically and only keep the icon. An icon must be set in this case.
		 */
		responsive: Boolean,
		/**
		 * `rel` attribute for when using with `link`
		 */
		rel: String,
		/**
		 * `role` attribute for when using with `link`
		 */
		role: String,
		/**
		 * Specific sizes for buttong styling
		 * @values `xs`, `sm`
		 */
		size: String,
		/**
		 * In connection with the `link` attribute, you can also set the
		 * target of the link. This does not apply to regular buttons.
		 */
		target: String,
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
		 */
		title: String,
		/**
		 * @deprecated 4.0 Use the `title` prop instead
		 * @todo button.prop.tooltip.deprecated - remove @ 5.0
		 */
		tooltip: String,
		/**
		 * The type attribute sets the button type like in HTML.
		 * @values button, submit, reset
		 */
		type: {
			type: String,
			default: "button"
		},
		/**
		 * Styling variants for the button
		 * @values `filled`, `dimmed`
		 */
		variant: String
	},
	computed: {
		attrs() {
			// Shared
			const attrs = {
				"aria-current": this.current,
				"aria-disabled": this.disabled,
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
	methods: {
		focus() {
			this.$el.focus?.();
		},
		onClick(e) {
			if (this.disabled) {
				e.preventDefault();
				return false;
			}

			this.click?.(e);
			this.$emit("click", e);
		}
	}
};
</script>

<style>
:root {
	--button-height: var(--height-md);
	--button-width: auto;
	--button-color-back: none;
	--button-color-text: currentColor;
	--button-color-icon: currentColor;
	--button-color-hover-back: none;
	--button-color-hover-text: currentColor;
	--button-color-hover-icon: var(--button-color-icon);
	--button-padding: var(--spacing-2);
	--button-rounded: var(--spacing-1);
	--button-text-display: block;
}

.k-button {
	position: relative;
	display: inline-flex;
	font-size: var(--text-sm);
	align-items: center;
	justify-content: center;
	gap: 0.5rem;
	padding-inline: var(--button-padding);
	white-space: nowrap;
	line-height: 1.25;
	border-radius: var(--button-rounded);
	background: var(--button-color-back);
	height: var(--button-height);
	min-width: max-content;
	width: var(--button-width);
	color: var(--button-color-text);
}

.k-button:where(:not([aria-disabled])):hover {
	--button-color-back: var(--button-color-hover-back);
	--button-color-icon: var(--button-color-hover-icon);
	--button-color-text: var(--button-color-hover-text);
}

.k-button > .k-icon {
	color: var(--button-color-icon);
}

.k-button .k-button-text {
	display: var(--button-text-display);
}

.k-button:where([data-variant="dimmed"]) {
	--button-color-icon: var(--theme-color-600, var(--color-black));
	--button-color-text: var(--color-text-dimmed);
}
.k-button:where([data-variant="dimmed"]):where([aria-current]) {
	--button-color-text: var(--color-text);
}

.k-button:where([data-variant="filled"]) {
	--button-color-back: var(--color-gray-300);
	--button-color-hover-back: var(--color-gray-400);
}

.k-button:where([data-theme]) {
	--button-color-icon: var(--theme-color-600);
	--button-color-text: var(--theme-color-text);
	--button-color-hover-icon: var(--theme-color-700);
	--button-color-hover-text: var(--theme-color-text);
}

.k-button:where([data-theme][data-variant="dimmed"]) {
	--button-color-text: var(--theme-color-700);
}

.k-button:where([data-theme][data-variant="filled"]) {
	--button-color-icon: var(--theme-color-700);
	--button-color-back: var(--theme-color-back);
	--button-color-text: var(--theme-color-text);
	--button-color-hover-back: var(--theme-color-hover);
	--button-color-hover-icon: var(--theme-color-700);
	--button-color-hover-text: var(--theme-color-text);
}

/** Responsive buttons **/
.k-button:where([data-responsive]) {
	justify-content: start;
}

@container (max-width: 30rem) {
	.k-button:where([data-responsive]) {
		--button-text-display: none;
		--button-width: var(--button-height);
		min-width: var(--button-width);
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
	--icon-size: 14px;
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
	--icon-size: 10px;
	width: max-content;
	margin-inline-start: -0.125rem;
}

/** Disabled button **/
.k-button:where([aria-disabled]) {
	cursor: not-allowed;
}
</style>
