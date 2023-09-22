<template>
	<k-inputbox
		v-bind="$props"
		:after="null"
		:before="null"
		:icon="null"
		type="list"
		variant="multiline"
	>
		<k-list-input v-bind="$props" @input="$emit('input', $event)" />
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/ListInput.vue";

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
.k-list-inputbox {
	--input-padding: var(--inputbox-padding-multiline);
}
</style>
