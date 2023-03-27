<template>
	<k-field
		v-bind="$props"
		:input="_uid"
		:counter="counterOptions"
		class="k-writer-field"
	>
		<k-input
			v-bind="$props"
			:after="after"
			:before="before"
			:icon="icon"
			theme="field"
		>
			<k-writer
				ref="input"
				v-bind="$props"
				:value="value"
				class="k-writer-field-input"
				@input="$emit('input', $event)"
			/>
		</k-input>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as Writer } from "@/components/Forms/Writer/Writer.vue";
import counter from "@/mixins/forms/counter.js";

export default {
	mixins: [Field, Input, Writer, counter],
	inheritAttrs: false,
	props: {
		maxlength: Number,
		minlength: Number
	},
	computed: {
		counterValue() {
			return this.$helper.string.stripHTML(this.value);
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>

<style>
.k-writer-field-input .ProseMirror,
/* ::before is used for the placeholder */
.k-writer-field-input::before {
	line-height: 1.5em;
	padding: 0.375rem 0.5rem;
}
</style>
