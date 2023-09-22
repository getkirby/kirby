<template>
	<component
		:is="'k-' + type + '-inputbox'"
		v-bind="$props"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import { props as InputboxProps } from "./Inputbox/Inputbox.vue";

export const props = {
	mixins: [InputboxProps],
	inheritAttrs: false
};

export default {
	mixins: [props]
};
</script>
