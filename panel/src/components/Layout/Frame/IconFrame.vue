<template>
	<k-frame v-bind="$props" element="figure" class="k-icon-frame">
		<span v-if="isEmoji" data-type="emoji">{{ icon }}</span>
		<k-icon v-else v-bind="{ color, type: icon, alt }" />
	</k-frame>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		alt: String,
		fit: String,
		ratio: String,
		cover: Boolean,
		back: String,
		theme: String,
		color: String,
		icon: String
	},
	computed: {
		isEmoji() {
			return this.$helper.string.hasEmoji(this.icon);
		}
	}
};
</script>

<style>
/* fix emoji alignment on high-res screens */
@media only screen and (-webkit-min-device-pixel-ratio: 2),
	not all,
	not all,
	not all,
	only screen and (min-resolution: 192dpi),
	only screen and (min-resolution: 2dppx) {
	.k-icon-frame [data-type="emoji"] {
		font-size: 1.25em;
	}
}
</style>
