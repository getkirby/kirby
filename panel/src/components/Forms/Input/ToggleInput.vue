<template>
	<k-choice-input
		:id="id"
		ref="input"
		:checked="value"
		:disabled="disabled"
		:label="label"
		class="k-toggle-input"
		type="checkbox"
		variant="toggle"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import Input from "@/mixins/input.js";
import { required as validateRequired } from "vuelidate/lib/validators";

export const props = {
	props: {
		autofocus: Boolean,
		disabled: Boolean,
		id: [Number, String],
		/**
		 * The text to display next to the toggle. This can either be a string
		 * that doesn't change when the toggle switches. Or an array with the
		 * first value for the `false` text and the second value for
		 * the `true` text.
		 */
		text: {
			type: [Array, String]
		},
		required: Boolean,
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
	watch: {
		value() {
			this.onInvalid();
		}
	},
	mounted() {
		this.onInvalid();

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
		onInvalid() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
		},
		select() {
			this.$refs.input.focus();
		}
	},
	validations() {
		return {
			value: {
				required: this.required ? validateRequired : true
			}
		};
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
	border: 1px solid var(--color-border);
}
</style>
