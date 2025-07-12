<template>
	<k-field
		v-bind="$props"
		:class="['k-number-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
	>
		<k-input
			v-bind="$props"
			ref="input"
			type="number"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as NumberInput } from "../Input/NumberInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-number-input>` for additional information.
 * @example <k-number-field :value="number" @input="number = $event" name="number" label="Number" />
 */
export default {
	mixins: [Field, Input, NumberInput],
	inheritAttrs: false,
	emits: ["input"],
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
