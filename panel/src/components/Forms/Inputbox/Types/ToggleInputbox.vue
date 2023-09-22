<template>
	<k-inputbox v-bind="$props" type="toggle" variant="box">
		<k-toggle-input v-bind="$props" @input="$emit('input', $event)" />
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/ToggleInput.vue";

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
.k-toggle-inputbox .k-toggle-input {
	padding: var(--inputbox-padding);
	flex-grow: 1;
}
.k-toggle-inputbox .k-inputbox-before {
	padding-inline-end: var(--spacing-1);
}
</style>
