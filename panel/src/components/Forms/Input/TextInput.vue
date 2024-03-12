<template>
	<k-string-input
		v-bind="$props"
		:class="['k-text-input', $attrs.class]"
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
 * @example <k-text-input :value="text" @input="text = $event" name="text" />
 */
export default {
	mixins: [StringInput, props]
};
</script>

<style>
.k-text-input {
	padding: var(--input-padding);
	border-radius: var(--input-rounded);
}
.k-text-input:focus {
	outline: 0;
}
.k-text-input[data-font="monospace"] {
	font-family: var(--font-mono);
}
.k-text-input:disabled::placeholder {
	opacity: 0;
}
</style>
