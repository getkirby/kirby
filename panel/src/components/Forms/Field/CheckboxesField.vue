<template>
	<k-field v-bind="$props" :counter="counterOptions" class="k-checkboxes-field">
		<k-checkboxes-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/CheckboxesInputbox.vue";
import Counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>` and `<k-checkboxes-inputbox>` for additional information.
 */
export default {
	mixins: [FieldProps, InputboxProps, Counter],
	inheritAttrs: false
};
</script>
