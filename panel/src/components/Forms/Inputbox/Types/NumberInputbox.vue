<template>
	<k-inputbox v-bind="$props" type="number">
		<k-number-input v-bind="$props" @input="$emit('input', $event)" />
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/NumberInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps]
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"]
};
</script>

<style>
.k-number-inputbox .k-number-input {
	padding-inline: var(--inputbox-padding);
	line-height: 1.25;
}
.k-number-inputbox .k-number-input:focus {
	outline: none;
}
</style>
