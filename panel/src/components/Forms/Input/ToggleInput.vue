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
 * @example <k-input :value="toggle" @input="toggle = $event" name="toggle" type="toggle" />
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
	},
	mounted() {
		if (this.$props.autofocus) {
			this.focus();
		}
	},
	methods: {
		onEnter(e) {
			if (e.key === "Enter") {
				this.$refs.input.click();
			}
		},
		onInput(checked) {
			this.$emit("input", checked);
		},
		select() {
			this.$refs.input.focus();
		}
	}
};
</script>

<style>
/* Toggle */
.k-input[data-type="toggle"] {
	--input-color-border: transparent;
	--input-shadow: var(--shadow);
}
.k-input[data-type="toggle"] .k-input-before {
	padding-inline-end: calc(var(--input-padding) / 2);
}
.k-input[data-type="toggle"] .k-toggle-input {
	padding-inline-start: var(--input-padding);
}
.k-input[data-type="toggle"][data-disabled] {
	box-shadow: none;
}
</style>
