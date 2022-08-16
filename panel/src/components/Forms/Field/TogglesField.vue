<template>
	<k-field v-bind="$props" class="k-toggles-field">
		<k-input
			:id="_uid"
			ref="input"
			:class="{ grow }"
			v-bind="$props"
			theme="field"
			type="toggles"
			v-on="$listeners"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TogglesInput } from "../Input/TogglesInput.vue";

export default {
	mixins: [Field, Input, TogglesInput],
	inheritAttrs: false,
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		onInput(value) {
			this.$emit("input", value);
		}
	}
};
</script>
