<template>
	<k-field
		v-bind="$props"
		:class="['k-toggles-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
	>
		<k-empty
			v-if="choices.length === 0"
			:text="$t('options.none')"
			icon="checklist"
		/>
		<k-input
			v-else
			ref="input"
			:class="{ grow }"
			v-bind="$props"
			:options="choices"
			type="toggles"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TogglesInput } from "../Input/TogglesInput.vue";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [Field, Input, TogglesInput],
	inheritAttrs: false,
	emits: ["input"],
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
