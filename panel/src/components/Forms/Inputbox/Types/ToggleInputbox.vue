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
.k-inputbox[data-type="toggle"] .k-inputbox-element {
	padding-inline: var(--input-padding);
}
</style>
