<template>
	<k-string-input
		v-bind="$props"
		class="k-text-input"
		type="text"
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
 * @public
 */
export default {
	mixins: [StringInput, props]
};
</script>
