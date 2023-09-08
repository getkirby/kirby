<template>
	<k-inputbox v-bind="$props" type="toggles" variant="plain">
		<k-inputbox-element slot="element" :data-grow="grow">
			<k-toggles-input v-bind="$props" @input="$emit('input', $event)" />
		</k-inputbox-element>
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/TogglesInput.vue";

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
.k-inputbox[data-type="toggles"]
	.k-inputbox-element[data-grow="true"]
	.k-toggles-input {
	width: 100%;
}
.k-inputbox[data-type="toggles"] .k-inputbox-before {
	padding-inline-end: var(--spacing-6);
}
.k-inputbox[data-type="toggles"] .k-inputbox-after {
	padding-inline-start: var(--spacing-6);
}
</style>
