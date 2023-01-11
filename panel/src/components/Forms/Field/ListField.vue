<template>
	<k-field v-bind="$props" :input="_uid" :counter="false" class="k-list-field">
		<k-input
			v-bind="$props"
			:id="_uid"
			ref="input"
			:marks="marks"
			:value="value"
			type="list"
			theme="field"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";

/**
 * Have a look at `<k-field>` and `<k-input>` for additional information.
 */
export default {
	mixins: [Field, Input],
	inheritAttrs: false,
	props: {
		marks: [Array, Boolean],
		value: String
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>

<style>
.k-list-field .k-list-input {
	padding: 0.375rem 0.5rem 0.375rem 0.75rem;
}
</style>
