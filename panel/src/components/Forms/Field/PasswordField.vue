<template>
	<k-field
		v-bind="$props"
		:class="['k-password-field', $attrs.class]"
		:counter="counterOptions"
		:input="id"
		:style="$attrs.style"
	>
		<template v-if="$slots.options" #options>
			<slot name="options" />
		</template>
		<k-input
			v-bind="$props"
			ref="input"
			type="password"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as PasswordInput } from "../Input/PasswordInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-password-input>` for additional information.
 */
export default {
	mixins: [Field, Input, PasswordInput, counter],
	inheritAttrs: false,
	props: {
		minlength: {
			type: Number,
			default: 8
		},
		icon: {
			type: String,
			default: "key"
		}
	},
	emits: ["input"],
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
