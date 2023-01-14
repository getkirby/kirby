<template>
	<component
		:is="component"
		ref="button"
		v-bind="attributes"
		v-on="$listeners"
		@click="click"
	>
		<k-icon v-if="icon" :type="icon" :alt="tooltip" class="k-button-icon" />

		<template v-if="text">
			{{ text }}
		</template>
		<span v-else-if="$slots.default" class="k-button-text"><slot /></span>
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
		autofocus: Boolean,
		/**
		 * Pass instead of a link URL to be triggered on clicking the button
		 */
		click: {
			type: Function,
			default: () => {}
		},
		/**
		 * Sets the `aria-current` attribute. Especially useful in connection with a `link` attribute.
		 */
		current: [String, Boolean],
		/**
		 * A disabled button will have no pointer events and the opacity is be reduced.
		 */
		disabled: Boolean,
		/**
		 * Adds an icon to the button.
		 */
		icon: String,
		id: [String, Number],
		/**
		 * If the link attribute is set, the button will automatically be converted to a proper `a` tag.
		 */
		link: String,
		/**
		 * A responsive button will hide the button text on smaller screens automatically and only keep the icon. An icon must be set in this case.
		 */
		responsive: Boolean,
		rel: String,
		role: String,
		/**
		 * In connection with the `link` attribute, you can also set the target of the link. This does not apply to regular buttons.
		 */
		target: String,
		tabindex: String,
		/**
		 * Use either the default slot or this prop for the button text
		 */
		text: [String, Number],
		/**
		 * With the theme you can control the general design of the button.
		 * @values positive, negative
		 */
		theme: String,
		/**
     * The tooltip attribute can be used to add additional text to the button, which is shown on mouseover (with the `title` attribute).

     */
		tooltip: String,
		/**
		 * The type attribute sets the button type like in HTML.
		 * @values button, submit, reset
		 */
		type: {
			type: String,
			default: "button"
		}
	},
	computed: {
		attributes() {
			const attributes = {
				class: "k-button",
				"data-responsive": this.responsive,
				"data-theme": this.theme,
				id: this.id,
				title: this.tooltip
			};

			// button only
			if (this.component === "button") {
				attributes["type"] = this.type;
				attributes["data-disabled"] = this.disabled;
				attributes["aria-disabled"] = this.disabled;
			}

			// link only
			if (this.component === "k-link") {
				attributes["rel"] = this.rel;
				attributes["target"] = this.target;
				attributes["to"] = this.link;
			}

			if (this.component === "span") {
				attributes["data-disabled"] = true;
			}

			// for buttons and enabled links
			if (this.component === "button" || this.component === "k-link") {
				attributes["aria-current"] = this.current;
				attributes["autofocus"] = this.autofocus;
				attributes["role"] = this.role;
				attributes["tabindex"] = this.tabindex;
			}

			return attributes;
		},
		component() {
			if (!this.link) {
				return "button";
			}

			if (!this.disabled) {
				return "k-link";
			}

			return "span";
		}
	},
	methods: {
		focus() {
			this.$refs.button.focus?.();
		},
		tab() {
			this.$refs.button.tab?.();
		},
		untab() {
			this.$refs.button.untab?.();
		}
	}
};
</script>

<style>
button {
	line-height: inherit;
	border: 0;
	font-family: var(--font-sans);
	font-size: 1rem;
	color: currentColor;
	background: none;
	cursor: pointer;
}
button::-moz-focus-inner {
	padding: 0;
	border: 0;
}

.k-button {
	display: inline-block;
	position: relative;
	font-size: var(--text-sm);
	transition: color 0.3s;
	outline: none;
}
.k-button:focus,
.k-button:hover {
	outline: none;
}

.k-button * {
	vertical-align: middle;
}

/* hide button text on small screens */
.k-button[data-responsive="true"] .k-button-text {
	display: none;
}
@media screen and (min-width: 30em) {
	.k-button[data-responsive="true"] .k-button-text {
		display: inline;
	}
}

.k-button[data-theme] {
	color: var(--theme);
}

.k-button-icon {
	display: inline-flex;
	align-items: center;
	line-height: 0;
}

.k-button-icon ~ .k-button-text {
	padding-inline-start: 0.5rem;
}

.k-button-text {
	opacity: 0.75;
}
.k-button:focus .k-button-text,
.k-button:hover .k-button-text {
	opacity: 1;
}

.k-button-text span,
.k-button-text b {
	vertical-align: baseline;
}

.k-button[data-disabled="true"] {
	opacity: 0.5;
	pointer-events: none;
	cursor: default;
}
.k-card-options > .k-button[data-disabled="true"] {
	display: inline-flex;
}
</style>
