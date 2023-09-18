<template>
	<k-field :input="_uid" v-bind="$props" class="k-range-field">
		<k-range-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/RangeInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-range-input>` for additional information.
 * @example <k-range-field :value="range" @input="range = $event" name="range" label="Slider" />
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false
};
</script>
