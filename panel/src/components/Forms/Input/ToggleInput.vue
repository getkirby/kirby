<template>
	<k-choice-input
		v-bind="$props"
		:checked="value"
		:label="label"
		class="k-toggle-input"
		type="checkbox"
		variant="toggle"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";

export const props = {
	mixins: [InputProps],
	props: {
		/**
		 * The text to display next to the toggle. This can either be a string
		 * that doesn't change when the toggle switches. Or an array with the
		 * first value for the `false` text and the second value for
		 * the `true` text.
		 */
		text: {
			type: [Array, String]
		},
		value: Boolean
	}
};

/**
 * @example <k-toggle-input :value="value" @input="value = $event" />
 * @public
 */
export default {
	mixins: [Input, props],
	computed: {
		label() {
			// Add fallback for text
			const text = this.text ?? [this.$t("off"), this.$t("on")];

			// If text differentiates between toggle state
			if (Array.isArray(text)) {
				return this.value ? text[1] : text[0];
			}

			return text;
		}
	}
};
</script>
