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
		image: [Object, Boolean],
		/**
		 * Width (e.g. `"1/2"`) of the parent column is used to set the srcset sizes accordingly
		 * @todo refactor to remove this
		 */
		width: {
			type: String,
			default: "1/1"
		}
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
				back: this.image.back ?? "gray-500",
				cover: true,
				...this.image,
				ratio: this.layout === "list" ? "auto" : this.image.ratio,
				size: this.sizes
			};
		},
		component() {
			return this.image.src ? "k-image-frame" : "k-icon-frame";
		},
		sizes() {
			switch (this.width) {
				case "1/2":
				case "2/4":
					return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 44em, 27em";
				case "1/3":
					return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 29.333em, 27em";
				case "1/4":
					return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 22em, 27em";
				case "2/3":
					return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 27em, 27em";
				case "3/4":
					return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 66em, 27em";
				default:
					return "(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 88em, 27em";
			}
		}
	}
};
</script>
