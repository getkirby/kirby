<template>
	<k-string-input
		v-bind="$props"
		type="text"
		class="k-text-input"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps]
};

/**
 * @example <k-text-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [StringInput, props]
};
</script>
