<template>
	<k-field
		v-bind="$props"
		:input="_uid"
		:counter="counterOptions"
		class="k-writer-field"
	>
		<k-writer-inputbox
			:id="_uid"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/WriterInputbox.vue";
import Counter from "@/mixins/forms/counter.js";

export default {
	mixins: [FieldProps, InputboxProps, Counter],
	inheritAttrs: false
};
</script>
