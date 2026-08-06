<template>
	<k-frame
		v-bind="$props"
		:class="['k-image-frame', 'k-image', $attrs.class]"
		:style="$attrs.style"
		element="figure"
	>
		<img
			v-if="src || resolvedSrc"
			:alt="alt ?? resolvedAlt ?? ''"
			:src="src ?? resolvedSrc"
			:srcset="srcset ?? resolvedSrcset"
			:sizes="sizes"
			:loading="lazy === true ? 'lazy' : 'eager'"
			decoding="async"
			@dragstart.prevent
		/>
	</k-frame>
</template>

<script>
import { props as FrameProps } from "./Frame.vue";

export const props = {
	mixins: [FrameProps],
	props: {
		/**
		 * Just like in regular `<img>` tags, you can and should define a proper `alt` attribute whenever possible. The component will add an empty alt tag when no alt text is specified to be skipped by screen readers. Otherwise the filename would be read.
		 */
		alt: String,
		/**
		 * File ID/UUID (can be used instead of `url`)
		 * @since 6.0.0
		 */
		file: String,
		/**
		 * Whether the image is only loaded once it comes into view.
		 * Disable for images that are visible right away.
		 * @since 6.0.0
		 */
		lazy: {
			type: Boolean,
			default: true
		},
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

/**
 * Use <k-image-frame> to display an image from an external URL
 * or internal file UUID in a fixed ratio with background etc.
 *
 * @example
 * <k-image-frame src="https://getkirby.com/image.jpg" ratio="16/9" back="pattern" />
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	data() {
		return {
			resolvedAlt: null,
			resolvedSrc: null,
			resolvedSrcset: null
		};
	},
	watch: {
		file: {
			handler: "fetch",
			immediate: true
		}
	},
	methods: {
		async fetch() {
			const file = this.file;
			let alt,
				src,
				srcset = null;

			// if internal file, load data for file UUID from request endpoint
			if (file) {
				const item = await this.$helper.items("items/files", file, {
					layout: "auto",
					image: JSON.stringify({
						ratio: this.ratio,
						cover: this.cover
					})
				});

				// the file might have changed while the request was in flight
				if (this.file !== file) {
					return;
				}

				alt = item?.alt;
				src = item?.image?.src;
				srcset = item?.image?.srcset;
			}

			this.resolvedAlt = alt;
			this.resolvedSrc = src;
			this.resolvedSrcset = srcset;
		}
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
