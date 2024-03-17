<template>
	<k-frame
		v-bind="$props"
		:class="['k-icon-frame', $attrs.class]"
		:style="$attrs.style"
		element="figure"
	>
		<k-icon v-bind="{ color, type: icon, alt }" />
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
	inheritAttrs: false
};
</script>

<style>
.k-icon-frame [data-type="emoji"] {
	overflow: visible;
}
</style>
