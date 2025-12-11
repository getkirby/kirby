<template>
	<k-frame
		v-bind="$props"
		:class="['k-image-frame', 'k-image', $attrs.class]"
		:style="$attrs.style"
		element="figure"
	>
		<img
			v-if="resolvedSrc"
			:alt="resolvedAlt ?? ''"
			:src="resolvedSrc"
			:srcset="resolvedSrcset"
			:sizes="sizes"
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
		 * For responsive images, pass the `sizes` attribute
		 */
		sizes: String,
		/**
		 * The path/URL to the image file
		 */
		src: {
			type: String,
			required: true
		},
		/**
		 * For responsive images, pass the `srcset` attribute
		 */
		srcset: String
	}
};

/**
 * Use <k-image-frame> to display an image from an external URL
 * or internal file UUID in a fixed ratio with background etc.
 * @since 4.0.0
 *
 * @example <k-image-frame src="https://getkirby.com/image.jpg" ratio="16/9" back="pattern" />
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
	computed: {
		isInternal() {
			return this.src.startsWith("file://") === true;
		}
	},
	watch: {
		src: {
			handler: "fetch",
			immediate: true
		}
	},
	methods: {
		async fetch() {
			if (this.isInternal === false) {
				this.resolvedAlt = this.alt;
				this.resolvedSrc = this.src;
				this.resolvedSrcset = this.srcset;
				return;
			}

			// if internal file, load data for file UUID from request endpoint
			const data = await await this.$panel.get("items/files", {
				query: {
					items: this.src,
					layout: "auto",
					image: JSON.stringify({
						ratio: this.ratio,
						cover: this.cover
					})
				}
			});

			this.resolvedAlt = data.items[0]?.alt;
			this.resolvedSrc = data.items[0]?.image.src;
			this.resolvedSrcset = data.items[0]?.image.srcset;
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
