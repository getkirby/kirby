<template>
	<k-field
		v-bind="$props"
		:counter="counterOptions"
		:class="['k-writer-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
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
import { props as WriterInput } from "@/components/Forms/Input/WriterInput.vue";
import counter from "@/mixins/forms/counter.js";

export default {
	mixins: [Field, Input, WriterInput, counter],
	inheritAttrs: false,
	emits: ["input"],
	computed: {
		counterValue() {
			const plain = this.$helper.string.stripHTML(this.value ?? "");
			return this.$helper.string.unescapeHTML(plain);
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
