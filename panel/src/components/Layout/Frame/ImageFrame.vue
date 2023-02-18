<template>
	<k-frame
		v-bind="$props"
		element="figure"
		class="k-image-frame k-image"
		v-on="$listeners"
	>
		<img
			:key="src"
			:alt="alt || ''"
			:src="src"
			:srcset="srcset"
			:sizes="sizes"
			@dragstart.prevent
		/>
	</k-frame>
</template>

<script>
/**
 * The `k-image` component simplifies loading
 * and sizing of images and their backgrounds.
 * It can be used as a replacement for regular
 * `<img>` tags, but has a bunch of additional
 * options and built-in lazy-loading.
 * @public
 *
 * @example <k-image src="myimage.jpg" />
 */
export default {
	inheritAttrs: false,
	props: {
		/**
		 * Just like in regular `<img>` tags,
		 * you can and should define a proper `alt`
		 * attribute whenever possible. The component
		 * will add an empty alt tag when no alt
		 * text is specified to be skipped by screen
		 * readers. Otherwise the filename would be read.
		 */
		alt: String,
		/**
		 * By default the background of
		 * images will be transparent
		 *
		 * @values black, white, pattern
		 */
		back: String,
		/**
		 * If images don't fit the defined ratio,
		 * the component will add additional space
		 * around images. You can change that behavior
		 * with the `cover` attribute. If `true`,
		 * the image will be cropped to fit the ratio.
		 */
		cover: Boolean,
		/**
		 * The container can be set to a fixed ratio.
		 * The ratio can be defined freely with the format
		 * `widthFraction/heightFraction`. The ratio will
		 * be calculated automatically.
		 *
		 * @values e.g. `1/1`, `16/9` or `4/5`
		 */
		ratio: String,
		/**
		 * For responsive images, pass the `sizes` attribute
		 */
		sizes: String,
		/**
		 * The path/URL to the image file
		 */
		src: String,
		/**
		 * For responsive images, pass the `srcset` attribute
		 */
		srcset: String
	}
};
</script>

<style>
.k-image[data-back="pattern"] {
	--back: var(--color-black) var(--pattern);
}
.k-image[data-back="black"] {
	--back: var(--color-black);
}
.k-image[data-back="white"] {
	--back: var(--color-white);
	color: var(--color-gray-900);
}
</style>
