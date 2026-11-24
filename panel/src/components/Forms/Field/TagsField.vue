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
			:options="choices"
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
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [Field, Input, TagsInput, counter],
	inheritAttrs: false,
	emits: ["input"],
	computed: {
		/**
		 * The options with an `[unknown]` option for each value without one,
		 * unless any value is accepted as custom tag
		 */
		choices() {
			if (this.accept === "all") {
				return this.options;
			}

			return this.$helper.field.options(this.options, this.value);
		},
		hasNoOptions() {
			return this.choices.length === 0 && this.accept === "options";
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
