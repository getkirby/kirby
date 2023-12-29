<template>
	<k-field v-bind="$props" :input="uid" :counter="false" class="k-list-field">
		<k-input
			v-bind="$props"
			:id="uid"
			ref="input"
			type="list"
			theme="field"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as List } from "@/components/Forms/Input/ListInput.vue";

/**
 * Have a look at `<k-field>` and `<k-input>` for additional information.
 */
export default {
	mixins: [Field, Input, List],
	inheritAttrs: false,
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>

<style>
.k-list-field .k-list-input .ProseMirror,
/* ::before is used for the placeholder */
.k-list-field .k-list-input::before {
	padding: 0.475rem 0.5rem 0.475rem 0.75rem;
}
</style>
