<template>
	<component
		:is="link ? 'k-link' : 'p'"
		:class="['k-bubble', $attrs.class]"
		:to="link"
		:style="{
			color: $helper.color(color),
			background: $helper.color(back),
			...$attrs.style
		}"
		@click.stop
	>
		<!-- @slot Replace the default image -->
		<slot name="image">
			<k-image-frame v-if="image?.src" v-bind="image" />
			<k-icon-frame v-else-if="image" v-bind="image" />
			<span v-else></span>
		</slot>
		<template v-if="text">
			<!-- eslint-disable-next-line vue/no-v-html -->
			<span v-if="html" class="k-bubble-text" v-html="text" />
			<span v-else class="k-bubble-text">{{ text }}</span>
		</template>
	</component>
</template>

<script>
/**
 * Bubble to display content in an inline context,
 * e.g. the structure preview table
 * @since 3.7.0
 * @deprecated 5.0.0 Use `<k-tags>` instead
 *
 * @example <k-bubble text="Hello" />
 */
export default {
	inheritAttrs: false,
	props: {
		/**
		 * Sets a custom background color. Either shorthand for Panel default colors or actual CSS value.
		 *
		 * @deprecated 4.0.0 Use `--bubble-back` CSS property instead
		 */
		back: String,
		/**
		 * Sets a custom color. Either shorthand for Panel default colors or actual CSS value.
		 *
		 * @deprecated 4.0.0 Use `--bubble-text` CSS property instead
		 */
		color: String,
		/**
		 * HTML element to use
		 */
		element: {
			type: String,
			default: "li"
		},
		/**
		 * If set to `true`, the `text` is rendered as HTML code,
		 * otherwise as plain text
		 */
		html: {
			type: Boolean
		},
		/**
		 * Optional image to display in the bubble. Used for <k-image-frame>, see for available props
		 */
		image: Object,
		/**
		 * Sets a link on the bubble. Link can be absolute or relative.
		 */
		link: String,
		/**
		 * Text to display in the bubble
		 */
		text: String
	},
	mounted() {
		window.panel.deprecated(
			"<k-bubble> will be removed in a future version. Use <k-tag> instead."
		);
	}
};
</script>

<style>
:root {
	--bubble-size: 1.525rem;
	--bubble-back: var(--panel-color-back);
	--bubble-rounded: var(--rounded-sm);
	--bubble-text: var(--color-black);
}

.k-bubble {
	width: min-content;
	height: var(--bubble-size);
	white-space: nowrap;
	line-height: 1.5;
	background: var(--bubble-back);
	color: var(--bubble-text);
	border-radius: var(--bubble-rounded);
	overflow: hidden;
}
.k-bubble .k-frame {
	width: var(--bubble-size);
	height: var(--bubble-size);
}
.k-bubble:has(.k-bubble-text) {
	display: flex;
	gap: var(--spacing-2);
	align-items: center;
	padding-inline-end: 0.5rem;
	font-size: var(--text-xs);
}
</style>
