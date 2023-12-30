<template>
	<input
		v-direction
		v-bind="{
			autocomplete,
			autofocus,
			disabled,
			id,
			maxlength,
			minlength,
			name,
			pattern,
			placeholder,
			required,
			spellcheck,
			type,
			value
		}"
		:aria-label="ariaLabel"
		:data-font="font"
		class="k-string-input"
		@input="$emit('input', $event.target.value)"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import {
	autocomplete,
	autofocus,
	font,
	maxlength,
	minlength,
	pattern,
	placeholder,
	spellcheck
} from "@/mixins/props.js";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength,
	email as validateEmail,
	url as validateUrl
} from "vuelidate/lib/validators";

export const props = {
	mixins: [
		InputProps,
		autocomplete,
		autofocus,
		font,
		maxlength,
		minlength,
		pattern,
		placeholder,
		spellcheck
	],
	props: {
		ariaLabel: String,
		preselect: Boolean,
		type: {
			default: "text",
			type: String
		},
		value: {
			type: String
		}
	}
};

/**
 * @since 4.0.0
 * @example <k-string-input :value="value" type="text" @input="value = $event" />
 */
export default {
	mixins: [Input, props],
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

		if (this.$props.preselect) {
			this.select();
		}
	},
	methods: {
		onInvalid() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
		},
		select() {
			this.$refs.input.select();
		}
	},
	validations() {
		const validateMatch = (value) => {
			return (
				(!this.required && !value) || !this.$refs.input.validity.patternMismatch
			);
		};

		return {
			value: {
				required: this.required ? validateRequired : true,
				minLength: this.minlength ? validateMinLength(this.minlength) : true,
				maxLength: this.maxlength ? validateMaxLength(this.maxlength) : true,
				email: this.type === "email" ? validateEmail : true,
				url: this.type === "url" ? validateUrl : true,
				pattern: this.pattern ? validateMatch : true
			}
		};
	}
};
</script>

<style>
.k-string-input {
	padding: var(--input-padding);
	border-radius: var(--input-rounded);
}
.k-string-input:focus {
	outline: 0;
}
.k-string-input[data-font="monospace"] {
	font-family: var(--font-mono);
}
</style>
