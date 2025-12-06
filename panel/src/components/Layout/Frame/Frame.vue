<template>
	<component
		:is="element"
		v-if="ratio"
		:class="['k-frame', $attrs.class]"
		:data-theme="theme"
		:style="{
			'--fit': fit ?? (cover ? 'cover' : 'contain'),
			'--ratio': ratio,
			'--back': background,
			...$attrs.style
		}"
	>
		<slot />
	</component>
	<slot v-else />
</template>

<script>
export const props = {
	props: {
		/**
		 * HTML element to use as container
		 */
		element: {
			type: String,
			default: "div"
		},
		/**
		 * Object fit value to user for the content
		 * @values contain, fill, cover
		 */
		fit: String,
		/**
		 * Aspect ratio for the container. The ratio can be defined freely with the format `widthFraction/heightFraction`.
		 *
		 *  @values e.g. `1/1`, `16/9` or `4/5`
		 */
		ratio: String,
		/**
		 * If the content doesn't fit the defined ratio, the component will add additional space around the content. You can change that behavior with the `cover` attribute. If `true`, the image will be cropped to fit the ratio.
		 */
		cover: Boolean,
		/**
		 * Background for the frame. Either shorthand for Panel default colors or actual CSS value.
		 */
		back: String,
		/**
		 * Theme to use for the frame
		 * @values "positive", "negative", "notice", "warning", "info", "passive", "white", "dark"
		 */
		theme: String
	}
};

/**
 * Use <k-frame> to display content in a fixed ratio with background etc.
 * @since 4.0.0
 *
 * @example <k-frame :ratio="1/1" :back="black">💛</k-frame>
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	computed: {
		background() {
			return this.$helper.color(this.back);
		}
	}
};
</script>

<style>
.k-frame {
	--fit: contain;
	--ratio: 1/1;

	position: relative;
	display: flex;
	justify-content: center;
	align-items: center;
	aspect-ratio: var(--ratio);
	background: var(--back);
	overflow: hidden;
}

.k-frame:where([data-theme]) {
	--back: var(--theme-color-back);
	color: var(--theme-color-text-highlight);
}

.k-frame *:where(img, video, iframe, button) {
	position: absolute;
	inset: 0;
	height: 100%;
	width: 100%;
	object-fit: var(--fit);
}
.k-frame > * {
	overflow: hidden;
	text-overflow: ellipsis;
	min-width: 0;
	min-height: 0;
}
</style>
