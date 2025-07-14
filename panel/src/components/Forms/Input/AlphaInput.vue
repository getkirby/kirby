<template>
	<k-range-input
		v-bind="$props"
		:min="0"
		:max="1"
		class="k-alpha-input"
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
			default: 0.01,
			type: Number
		},
		tooltip: {
			default: false,
			type: [Boolean, Object]
		}
	}
};

/**
 * Input to select a color's alpha value.
 * @since 4.0.0
 * @example <k-alpha-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [Input, props],
	emits: ["input"]
};
</script>

<style>
.k-alpha-input {
	--range-track-back: linear-gradient(to right, transparent, currentColor);
	--range-track-height: var(--range-thumb-size);

	color: black;
	background: white var(--pattern-light);
}
</style>
