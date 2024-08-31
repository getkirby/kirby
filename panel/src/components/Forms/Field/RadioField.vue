<template>
	<k-field
		v-bind="$props"
		:class="['k-radio-field', $attrs.class]"
		:input="id + '-0'"
		:style="$attrs.style"
	>
		<k-empty
			v-if="!options?.length"
			:text="$t('options.none')"
			icon="checklist"
		/>
		<k-radio-input
			v-else
			ref="input"
			v-bind="$props"
			@input="$emit('input', $event)"
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
