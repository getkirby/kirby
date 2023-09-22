<template>
	<k-inputbox
		v-if="options?.length"
		v-bind="$props"
		type="toggles"
		variant="plain"
	>
		<k-inputbox-element slot="element" :data-grow="grow">
			<k-toggles-input v-bind="$props" @input="$emit('input', $event)" />
		</k-inputbox-element>
	</k-inputbox>
	<k-empty v-else :icon="icon" :text="$t('options.none')" />
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
.k-toggles-inputbox .k-inputbox-element[data-grow="true"] .k-toggles-input {
	width: 100%;
}
.k-toggles-inputbox .k-inputbox-before {
	padding-inline-end: var(--spacing-6);
}
.k-toggles-inputbox .k-inputbox-after {
	padding-inline-start: var(--spacing-6);
}
</style>
