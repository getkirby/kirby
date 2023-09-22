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
			default: "email",
			type: String
		},
		placeholder: {
			default: () => window.panel.$t("email.placeholder"),
			type: String
		}
	}
};

/**
 * @example <k-email-input :value="value" @input="value = $event" />
 * @public
 */
export default {
	mixins: [StringInput, props]
};
</script>
