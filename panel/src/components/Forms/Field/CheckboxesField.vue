<template>
	<k-field
		v-bind="$props"
		:input="id + '-0'"
		:counter="counterOptions"
		class="k-checkboxes-field"
	>
		<k-empty
			v-if="choices.length === 0"
			:text="$t('options.none')"
			icon="checklist"
		/>
		<k-checkboxes-input
			v-else
			ref="input"
			v-bind="$props"
			:options="choices"
			v-on="$listeners"
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
	computed: {
		/**
		 * The options with an `[unknown]` option for each value without one
		 */
		choices() {
			return this.$helper.field.options(this.options ?? [], this.value);
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
