<template>
	<k-field
		v-bind="$props"
		:class="['k-toggle-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
	>
		<k-toggle-input
			v-bind="$props"
			ref="input"
			type="toggle"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as ToggleInput } from "../Input/ToggleInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-toggle-input>`
 * for additional information.
 * @example <k-toggle-field :value="toggle" @input="toggle = $event" label="Toggle" name="toggle" />
 */
export default {
	mixins: [Field, Input, ToggleInput],
	inheritAttrs: false,
	emits: ["input"],
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
