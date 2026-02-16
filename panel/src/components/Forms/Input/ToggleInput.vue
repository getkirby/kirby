<template>
	<k-choice-input
		v-bind="$props"
		:checked="value"
		:class="['k-toggle-input', $attrs.class]"
		:disabled="disabled"
		:icon="undefined"
		:label="labelText"
		:style="$attrs.style"
		type="checkbox"
		variant="toggle"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import Input from "@/mixins/input.js";
import { props as ChoiceInputProps } from "./ChoiceInput.vue";

export const props = {
	mixins: [ChoiceInputProps],
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
	emits: ["input"],
	computed: {
		labelText() {
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
				this.$el.click();
			}
		},
		onInput(checked) {
			this.$emit("input", checked);
		},
		select() {
			this.$el.focus();
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
.k-input[data-type="toggle"][data-disabled="true"] {
	box-shadow: none;
	border: 1px solid var(--color-border);
}
</style>
