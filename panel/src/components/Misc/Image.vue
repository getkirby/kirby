<template>
	<span
		:data-ratio="ratio"
		:data-back="back"
		:data-cover="cover"
		:style="{ 'aspect-ratio': ratio }"
		class="k-image"
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
	</span>
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
.k-image {
	position: relative;
	display: block;
	line-height: 0;
}
.k-image img {
	position: absolute;
	inset: 0;
	width: 100%;
	height: 100%;
	object-fit: contain;
}
.k-image[data-cover="true"] img {
	object-fit: cover;
}
.k-image[data-back="black"] {
	background: var(--color-black);
}
.k-image[data-back="white"] {
	background: var(--color-white);
	color: var(--color-gray-900);
}
.k-image[data-back="pattern"] {
	background: var(--color-black) var(--pattern);
}
</style>
