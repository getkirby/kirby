<template>
	<k-field
		v-bind="$props"
		:class="['k-text-field', $attrs.class]"
		:counter="counterOptions"
		:input="id"
		:style="$attrs.style"
	>
		<template #options>
			<slot name="options" />
		</template>
		<k-input
			v-bind="$props"
			ref="input"
			:type="inputType"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TextInput } from "../Input/TextInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-text-input>`
 * for additional information.
 * @example <k-text-field :value="text" @input="text = $event" name="text" label="Boring text" />
 */
export default {
	mixins: [Field, Input, TextInput, counter],
	inheritAttrs: false,
	emits: ["input"],
	computed: {
		inputType() {
			if (this.$helper.isComponent(`k-${this.type}-input`)) {
				return this.type;
			}

			return "text";
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		select() {
			this.$refs.input.select();
		}
	}
};
</script>

<style>
.k-field-counter {
	display: none;
}
.k-text-field:focus-within .k-field-counter {
	display: block;
}
</style>
