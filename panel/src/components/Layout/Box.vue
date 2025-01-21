<template>
	<component
		:is="element"
		:data-align="align"
		:data-theme="theme"
		:type="type"
		:style="height ? { '--box-height': height } : null"
		class="k-box"
	>
		<k-icon v-if="icon" :type="icon" />
		<!--
			@slot Box content, replaces content from `text` prop
			@binding {string} text
			@binding {boolean} html
		-->
		<slot v-bind="{ html, text }">
			<k-text v-if="html" :html="text" />
			<k-text v-else>
				{{ text }}
			</k-text>
		</slot>
	</component>
</template>

<script>
/**
 * The `<k-box>` component is a multi-purpose box with text. You can use it as a foundation for empty state displays or anything else that needs to be displayed in a box.
 *
 * @example <k-box text="This is a nice box" theme="positive" />
 */
export default {
	props: {
		/**
		 * @values "start", "center"
		 */
		align: {
			type: String,
			default: "start"
		},
		/**
		 * Whether the box should function as a button
		 */
		button: Boolean,
		/**
		 * CSS value for the height of the box
		 */
		height: String,
		/**
		 * Optional icon to display in the box
		 */
		icon: String,
		/**
		 * Choose one of the pre-defined styles
		 * @values "positive", "negative", "notice", "warning", "info", "passive", "text", "dark", "code", "empty"
		 */
		theme: {
			type: String
		},
		/**
		 * Text to display inside the box
		 */
		text: String,
		/**
		 * If set to `true`, the `text` is rendered as HTML code, otherwise as plain text
		 */
		html: {
			type: Boolean
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
:root {
	--box-height: var(
		--field-input-height
	); /* TODO: change back to --height-md after input refactoring */
	--box-padding-inline: var(--spacing-2);
	--box-font-size: var(--text-sm);
	--box-color-back: none;
	--box-color-text: currentColor;
}

.k-box {
	--icon-color: var(--box-color-icon);
	--text-font-size: var(--box-font-size);

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
	--box-color-text: var(--theme-color-text-highlight);
	--box-color-icon: var(--theme-color-700);
	--link-color: var(--box-color-text);
	--link-color-hover: var(--box-color-text);
	min-height: var(--box-height);
	line-height: 1.25;
	padding: 0.375rem var(--box-padding-inline);
	border-radius: var(--rounded);
}

.k-box[data-theme="text"],
.k-box[data-theme="white"] {
	box-shadow: var(--shadow);
}
.k-box[data-theme="text"] {
	padding: var(--spacing-6);
}
.k-box[data-theme="none"] {
	padding: 0;
}

/* Align:center */
.k-box[data-align="center"] {
	justify-content: center;
}
</style>
