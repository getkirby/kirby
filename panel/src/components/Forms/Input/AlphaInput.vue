<template>
	<k-range-input
		v-bind="$props"
		:min="0"
		:max="1"
		:step="0.01"
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
		max: {
			default: 1,
			type: Number
		},
		min: {
			default: 0,
			type: Number
		},
		step: {
			default: 0.01,
			type: Number
		}
	}
};

/**
 * @example <k-alpha-input :value="value" @input="value = $event" />
 * @public
 */
export default {
	mixins: [Input, props]
};
</script>

<style>
.k-alpha-input {
	--range-track-back: linear-gradient(to right, transparent, currentColor);
	color: black;
	background: var(--color-white) var(--pattern-light);
}
</style>
