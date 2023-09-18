<template>
	<input
		ref="range"
		v-bind="$props"
		:min="0"
		:max="360"
		:step="1"
		class="k-hue-input"
		type="range"
		@input="$emit('input', Number($event.target.value))"
	/>
</template>

<script>
import { autofocus, disabled, id, name, required } from "@/mixins/props.js";

export const props = {
	mixins: [autofocus, disabled, id, name, required],
	props: {
		value: Number
	}
};

/**
 * @example <k-hue-input :value="hue" @input="hue = $event" />
 */
export default {
	mixins: [props],
	inheritAttrs: false
};
</script>

<style>
.k-hue-input {
	--range-track-back: linear-gradient(
			to right,
			hsl(0, 100%, 50%) 0%,
			hsl(60, 100%, 50%) 16.67%,
			hsl(120, 100%, 50%) 33.33%,
			hsl(180, 100%, 50%) 50%,
			hsl(240, 100%, 50%) 66.67%,
			hsl(320, 100%, 50%) 83.33%,
			hsl(360, 100%, 50%) 100%
		)
		no-repeat;
}
</style>
