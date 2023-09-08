<template>
	<k-inputbox v-bind="$props" type="radio" variant="choices">
		<k-radio-input v-bind="$props" @input="$emit('input', $event)" />
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/RadioInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps]
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"]
};
</script>
