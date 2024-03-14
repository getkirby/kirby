<template>
	<k-field
		v-bind="$props"
		:class="['k-tags-field', $attrs.class]"
		:counter="counterOptions"
		:input="id"
		:style="$attrs.style"
	>
		<k-empty v-if="hasNoOptions" :icon="icon" :text="$t('options.none')" />
		<k-input
			v-else
			v-bind="$props"
			ref="input"
			type="tags"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TagsInput } from "../Input/TagsInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-tags-input>` for additional information.
 */
export default {
	mixins: [Field, Input, TagsInput, counter],
	inheritAttrs: false,
	computed: {
		hasNoOptions() {
			return this.options.length === 0 && this.accept === "options";
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
