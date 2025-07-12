<template>
	<k-range-input
		v-bind="$props"
		:min="0"
		:max="360"
		class="k-hue-input"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import { props as RangeInputProps } from "./RangeInput.vue";
import Input from "./RangeInput.vue";

export const props = {
	mixins: [RangeInputProps],
	props: {
		// unset unused/fixed props
		max: null,
		min: null,

		step: {
			default: 1,
			type: Number
		},
		tooltip: {
			default: false,
			type: [Boolean, Object]
		}
	}
};

/**
 * @since 4.0.0
 * @example <k-hue-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [Input, props],
	emits: ["input"]
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
	--range-track-height: var(--range-thumb-size);
}
</style>
