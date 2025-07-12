<template>
	<k-field
		v-bind="$props"
		:class="['k-select-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
	>
		<k-input
			v-bind="$props"
			ref="input"
			type="select"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as SelectInput } from "../Input/SelectInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-select-input>` for additional information.
 */
export default {
	mixins: [Field, Input, SelectInput],
	inheritAttrs: false,
	props: {
		icon: {
			type: String,
			default: "angle-down"
		}
	},
	emits: ["input"],
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
