<template>
	<k-field
		v-bind="$props"
		:input="_uid"
		:counter="counterOptions"
		class="k-writer-field"
	>
		<k-input
			v-bind="$props"
			ref="input"
			:after="after"
			:before="before"
			:icon="icon"
			theme="field"
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

<style>
.k-writer-field .k-writer-input .ProseMirror,
/* ::before is used for the placeholder */
.k-writer-field .k-writer-input::before {
	padding: 0.475rem var(--field-input-padding);
	line-height: 1.5;
}
</style>
