<template>
	<k-inputbox v-bind="$props" type="password">
		<k-password-input v-bind="$props" @input="$emit('input', $event)" />
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/PasswordInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		autocomplete: {
			default: "new-password",
			type: String
		},
		icon: {
			default: "key",
			type: String
		},
		minlength: {
			type: Number,
			default: 8
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"]
};
</script>

<style>
.k-inputbox[data-type="password"] .k-password-input {
	padding: var(--input-padding);
}
</style>
