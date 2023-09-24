<template>
	<k-image-icon-frame v-bind="attrs" class="k-item-image" />
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		image: [Object, Boolean],
		/**
		 * Display layout
		 * @values list, cards, cardlets
		 */
		layout: {
			type: String,
			default: "list"
		},
		width: String
	},
	computed: {
		attrs() {
			return {
				back: this.image.back ?? "gray-500",
				cover: true,
				...this.image,
				ratio: this.layout === "list" ? "1/1" : this.image.ratio,
				size: this.sizes
			};
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
