<template>
	<k-field v-bind="$props" :input="id" class="k-range-field">
		<k-input
			v-bind="$props"
			ref="input"
			type="range"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as RangeInput } from "../Input/RangeInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-range-input>` for additional information.
 * @example <k-range-field :value="range" @input="range = $event" name="range" label="Slider" />
 */
export default {
	mixins: [Input, Field, RangeInput],
	inheritAttrs: false,
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
