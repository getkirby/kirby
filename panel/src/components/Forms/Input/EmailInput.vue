<template>
	<k-string-input
		v-bind="$props"
		class="k-email-input"
		type="email"
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
			default: "email"
		},
		placeholder: {
			type: String,
			default: () => window.panel.$t("email.placeholder")
		}
	}
};

/**
 * @example <k-email-input :value="email" @input="email = $event" name="email" />
 */
export default {
	mixins: [StringInput, props]
};
</script>
