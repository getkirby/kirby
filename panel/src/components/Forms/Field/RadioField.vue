<template>
	<k-field v-bind="$props" :input="id + '-0'" class="k-radio-field">
		<k-empty
			v-if="choices.length === 0"
			:text="$t('options.none')"
			icon="checklist"
		/>
		<k-radio-input
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
import { props as RadioInput } from "../Input/RadioInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-radio-input>` for additional information.
 */
export default {
	mixins: [Field, Input, RadioInput],
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
