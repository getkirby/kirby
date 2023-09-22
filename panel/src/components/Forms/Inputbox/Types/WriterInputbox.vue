<template>
	<k-inputbox
		v-bind="$props"
		:after="null"
		:before="null"
		:icon="null"
		type="writer"
		variant="multiline"
	>
		<k-writer-input v-bind="$props" @input="$emit('input', $event)" />
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/WriterInput.vue";

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
.k-writer-inputbox {
	--input-padding: var(--inputbox-padding-multiline);
}
</style>
