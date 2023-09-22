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
.k-password-inputbox .k-password-input {
	padding: var(--inputbox-padding);
}
.k-password-inputbox .k-password-input:focus {
	outline: 0;
}
</style>
