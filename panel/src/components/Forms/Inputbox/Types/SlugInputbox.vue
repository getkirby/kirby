<template>
	<k-inputbox v-bind="$props" type="slug">
		<k-slug-input v-bind="$props" @input="$emit('input', $event)" />
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/SlugInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		icon: {
			default: "url",
			type: String
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
.k-inputbox[data-type="slug"] .k-slug-input {
	padding: var(--input-padding);
}
</style>
