<template>
	<k-field v-bind="$props" class="k-radio-field">
		<k-radio-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/RadioInputbox.vue";
import Counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>` and `<k-radio-inputbox>` for additional information.
 */
export default {
	mixins: [FieldProps, InputboxProps, Counter],
	inheritAttrs: false
};
</script>
