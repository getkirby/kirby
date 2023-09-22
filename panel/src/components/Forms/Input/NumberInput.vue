<template>
	<input
		v-bind="{
			autofocus,
			disabled,
			id,
			max,
			min,
			name,
			placeholder,
			required,
			step,
			value
		}"
		class="k-number-input"
		type="number"
		@input="$emit('input', $event.target.valueAsNumber)"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { pattern, placeholder } from "@/mixins/props.js";

export const props = {
	mixins: [InputProps, pattern, placeholder],
	props: {
		max: Number,
		min: Number,
		/**
		 * The amount to increment with each input step. This can be a decimal.
		 */
		step: Number,
		value: {
			type: Number
		}
	}
};

/**
 * @example <k-number-input :value="value" @input="value = $event" />
 * @public
 */
export default {
	mixins: [Input, props]
};
</script>
