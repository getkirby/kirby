<template>
	<k-inputbox
		v-if="options?.length"
		v-bind="$props"
		type="checkboxes"
		variant="choices"
	>
		<k-checkboxes-input v-bind="$props" @input="$emit('input', $event)" />
	</k-inputbox>
	<k-empty v-else :icon="icon" :text="$t('options.none')" />
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/CheckboxesInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps]
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"]
};
</script>
