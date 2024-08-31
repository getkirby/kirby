<template>
	<k-field
		v-bind="$props"
		:class="['k-textarea-field', $attrs.class]"
		:counter="counterOptions"
		:input="id"
		:style="$attrs.style"
	>
		<k-input
			v-bind="$props"
			ref="input"
			type="textarea"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TextareaInput } from "../Input/TextareaInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-textarea-input>`
 * for additional information.
 * @example <k-textarea-field :value="text" @input="text = $event" name="text" label="Text" />
 */
export default {
	mixins: [Field, Input, TextareaInput, counter],
	inheritAttrs: false,
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
