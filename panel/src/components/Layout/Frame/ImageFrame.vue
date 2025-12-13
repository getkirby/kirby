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
			:sizes="sizes ?? autoSizes"
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
 * @since 4.0.0
 *
 * @example <k-image-frame src="https://getkirby.com/image.jpg" ratio="16/9" back="pattern" />
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	data() {
		return {
			autoSizes: null,
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
	mounted() {
		this.$panel.observers.frames.observe(this.$el);

		this.$el.addEventListener("resize", (e) => {
			this.autoSizes = (e.detail?.width ?? 0) + "px";
		});
	},
	beforeUnmount() {
		this.$panel.observers.frames.unobserve(this.$el);
	},
	methods: {
		async fetch() {
			let alt,
				src,
				srcset = null;

			// if internal file, load data for file UUID from request endpoint
			if (this.file) {
				const data = await this.$panel.get("items/files", {
					query: {
						items: this.file,
						layout: "auto",
						image: JSON.stringify({
							ratio: this.ratio,
							cover: this.cover
						})
					}
				});

				alt = data.items[0]?.alt;
				src = data.items[0]?.image.src;
				srcset = data.items[0]?.image.srcset;
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
