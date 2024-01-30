<template>
	<k-field
		v-bind="$props"
		:input="id"
		:counter="counterOptions"
		class="k-writer-field"
	>
		<k-input
			v-bind="$props"
			ref="input"
			:after="after"
			:before="before"
			:icon="icon"
			type="writer"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as Writer } from "@/components/Forms/Input/WriterInput.vue";
import counter from "@/mixins/forms/counter.js";

export default {
	mixins: [Field, Input, Writer, counter],
	inheritAttrs: false,
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
