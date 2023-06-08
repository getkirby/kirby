<template>
	<component
		:is="element"
		:data-align="align"
		:data-theme="theme"
		:type="type"
		:style="height ? '--box-height: ' + height : null"
		class="k-box"
	>
		<k-icon v-if="icon" v-bind="icon" />
		<!-- @slot Use instead of `text` prop -->
		<slot>
			<k-text v-if="html" :html="text" />
			<k-text v-else>
				{{ text }}
			</k-text>
		</slot>
	</component>
</template>

<script>
/**
 * The `<k-box>` component is a multi-purpose
 * box with text. You can use it as a foundation
 * for empty state displays or anything else
 * that needs to be displayed in a box. It comes
 * with several pre-defined styles …
 * @public
 *
 * @example <k-box text="This is a nice box" theme="positive" />
 */
export default {
	props: {
		/**
		 * @values center
		 */
		align: String,
		button: Boolean,
		height: String,
		icon: Object,
		/**
		 * Choose one of the pre-defined styles
		 * @values none, code, button, positive, negative, notice, info, empty
		 */
		theme: {
			type: String,
			default: "none"
		},
		/**
		 * Text to display inside the box
		 */
		text: String,
		/**
		 * If set to `true`, the `text` is rendered as HTML code, otherwise as plain text
		 */
		html: {
			type: Boolean,
			default: false
		}
	},
	computed: {
		element() {
			return this.button ? "button" : "div";
		},
		type() {
			return this.button ? "button" : null;
		}
	}
};
</script>

<style>
.k-box {
	--box-color-back: none;
	--box-color-text: currentColor;
	--box-padding-inline: var(--spacing-2);
	--box-height: var(
		--field-input-height
	); /* TODO: change back to --height-md after inptu refactoring */
	--text-font-size: var(--text-sm);
	--icon-color: var(--box-color-icon);
	display: flex;
	width: 100%;
	align-items: center;
	gap: var(--spacing-2);
	color: var(--box-color-text);
	background: var(--box-color-back);
	word-wrap: break-word;
}

/* Themes */
.k-box[data-theme] {
	--box-color-back: var(--theme-color-back);
	--box-color-text: var(--theme-color-text);
	--box-color-icon: var(--theme-color-icon);
	min-height: var(--box-height);
	line-height: 1.25;
	padding: 0.375rem var(--box-padding-inline);
	border-radius: var(--rounded);
}

/* Text Box */
.k-box[data-theme]:has(> .k-text) {
	max-width: max-content;
}
.k-box[data-theme] > .k-text {
	padding: var(--spacing-3);
	margin-inline: auto;
}

/* Align:center */
.k-box[data-align="center"] {
	justify-content: center;
}
</style>
