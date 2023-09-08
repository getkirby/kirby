<template>
	<k-inputbox
		v-bind="$props"
		:after="null"
		:before="null"
		:icon="null"
		type="textarea"
		variant="multiline"
	>
		<k-textarea-input v-bind="$props" @input="$emit('input', $event)" />
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/TextareaInput.vue";

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
.k-inputbox[data-type="textarea"] .k-textarea-input-native {
	padding: var(--input-padding-multiline);
}
</style>
