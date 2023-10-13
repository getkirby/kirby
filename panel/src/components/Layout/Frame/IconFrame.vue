<template>
	<k-frame v-bind="$props" element="figure" class="k-icon-frame">
		<span v-if="isEmoji" data-type="emoji">{{ icon }}</span>
		<k-icon v-else v-bind="{ color, type: icon, alt }" />
	</k-frame>
</template>

<script>
import { props as FrameProps } from "./Frame.vue";
import { props as IconProps } from "@/components/Misc/Icon.vue";

export const props = {
	mixins: [FrameProps, IconProps],
	props: {
		/**
		 * Unset unused props from mixin
		 */
		type: null,

		/**
		 * Name of the chosen icon
		 * @see https://getkirby.com/docs/reference/panel/icons
		 */
		icon: String
	}
};

/**
 * Use <k-icon-frame> to display an icon in a fixed ratio with background etc.
 * @since 4.0.0
 *
 * @example <k-icon-frame icon="home" ratio="1/1" back="black" />
 */
export default {
	mixins: [props],
	inheritAttrs: false,
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
