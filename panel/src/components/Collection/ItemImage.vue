<template>
	<component
		:is="component"
		v-bind="attrs"
		:class="['k-item-image', $attrs.class]"
		:style="$attrs.style"
	/>
</template>

<script>
import { layout } from "@/mixins/props.js";

export const props = {
	mixins: [layout],
	props: {
		/**
		 * See `<k-image-frame>` or `<k-icon-frame>` for all available options
		 */
		image: [Object, Boolean]
	}
};

/**
 * Displays a preview image or icon for a collection `<k-item>`
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	computed: {
		attrs() {
			return {
				back: this.image.back,
				cover: true,
				...this.image,
				ratio: this.layout === "list" ? "auto" : this.image.ratio
			};
		},
		component() {
			return this.image.src ? "k-image-frame" : "k-icon-frame";
		}
	}
};
</script>

<style>
.k-item-image {
	--back: var(--item-color-image);
	--icon-color: var(--item-color-icon);
}
</style>
