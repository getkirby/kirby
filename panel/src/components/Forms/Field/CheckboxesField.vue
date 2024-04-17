<template>
	<k-field
		v-bind="$props"
		:class="['k-checkboxes-field', $attrs.class]"
		:counter="counterOptions"
		:input="id + '-0'"
		:style="$attrs.style"
	>
		<k-empty
			v-if="!options?.length"
			:text="$t('options.none')"
			icon="checklist"
		/>
		<k-checkboxes-input
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
import { props as CheckboxesInput } from "../Input/CheckboxesInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-checkboxes-input>` for additional information.
 */
export default {
	mixins: [Field, Input, CheckboxesInput, counter],
	inheritAttrs: false,
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
