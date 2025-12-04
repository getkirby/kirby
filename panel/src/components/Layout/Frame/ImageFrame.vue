<template>
	<k-frame
		v-bind="$props"
		:class="['k-image-frame', 'k-image', $attrs.class]"
		:style="$attrs.style"
		element="figure"
	>
		<img
			v-if="src"
			:key="src"
			:alt="alt ?? ''"
			:src="src"
			:srcset="srcset"
			:sizes="size + 'px'"
			loading="lazy"
			@dragstart.prevent
		/>
		<span>{{ size }}</span>
	</k-frame>
</template>

<script>
import { props as FrameProps } from "./Frame.vue";

const observer = new ResizeObserver((entries) => {
	for (const index in entries) {
		const item = entries[index];
		item.target.dispatchEvent(
			new CustomEvent("resize", {
				detail: {
					width: Math.round(item.contentRect.width)
				}
			})
		);
	}
});

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
		src: String,
		/**
		 * For responsive images, pass the `srcset` attribute
		 */
		srcset: String
	}
};

/**
 * Use <k-image-frame> to display an image in a fixed ratio with background etc.
 * @since 4.0.0
 *
 * @example <k-image-frame src="https://getkirby.com/image.jpg" ratio="16/9" back="pattern" />
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	data() {
		return {
			size: 0
		};
	},
	mounted() {
		observer.observe(this.$el);

		const sizes = [200, 400, 800, 1200, 2400];
		this.width = 0;
		this.size = 0;

		this.$el.addEventListener("resize", (e) => {
			// find the best size according to e.detail.?width
			const width = e.detail?.width ?? 0;

			if (width === this.width) {
				return;
			}

			this.width = width;

			const bestSize =
				sizes.find((size) => size >= width) ?? sizes[sizes.length - 1];

			this.size = bestSize;
		});
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
.k-image-frame span {
	position: absolute;
	inset: 0;
}
</style>
