<template>
	<k-field
		v-bind="$props"
		:input="_uid"
		:counter="counterOptions"
		class="k-multiselect-field"
		@blur="blur"
	>
		<k-input
			v-bind="$props"
			:id="_uid"
			ref="input"
			theme="field"
			type="multiselect"
			v-on="$listeners"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as MultiselectInput } from "../Input/TagsInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-multiselect-input>` for additional information.
 */
export default {
	mixins: [Field, Input, MultiselectInput, counter],
	inheritAttrs: false,
	props: {
		icon: {
			type: String,
			default: "angle-down"
		}
	},
	mounted() {
		this.$refs.input.$el.setAttribute("tabindex", 0);
	},
	methods: {
		blur(e) {
			this.$refs.input.blur(e);
		},
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
