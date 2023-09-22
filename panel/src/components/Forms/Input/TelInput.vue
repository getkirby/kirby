<template>
	<k-string-input
		v-bind="$props"
		class="k-tel-input"
		type="tel"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		autocomplete: {
			default: "tel",
			type: String
		},
		placeholder: {
			default: () => window.panel.$t("tel.placeholder"),
			type: String
		}
	}
};

/**
 * @example <k-tel-input :value="value" @input="value = $event" />
 * @public
 */
export default {
	mixins: [StringInput, props]
};
</script>
