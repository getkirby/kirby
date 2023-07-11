<template>
	<k-field v-bind="$props" class="k-radio-field">
		<k-input
			v-bind="$props"
			:id="_uid"
			ref="input"
			:data-empty="!value"
			theme="field"
			type="radio"
			v-on="$listeners"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as RadioInput } from "../Input/RadioInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-radio-input>` for additional information.
 */
export default {
	mixins: [Field, Input, RadioInput],
	inheritAttrs: false,
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
