<template>
	<k-string-input
		v-bind="$props"
		class="k-password-input"
		type="password"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		autocomplete: {
			type: String,
			default: "new-password"
		},
		minlength: {
			default: 8,
			type: Number
		}
	}
};

/**
 * @example <k-password-input :value="value" @input="value = $event" />
 * @public
 */
export default {
	mixins: [StringInput, props]
};
</script>
