<template>
	<input
		ref="input"
		v-bind="{
			autocomplete,
			autofocus,
			disabled,
			id,
			minlength,
			name,
			pattern,
			placeholder,
			required,
			spellcheck,
			type,
			value
		}"
		v-direction
		:data-font="font"
		class="k-text-input"
		v-on="listeners"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import {
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
		font,
		maxlength,
		minlength,
		pattern,
		placeholder,
		spellcheck
	],
	props: {
		autocomplete: {
			type: [Boolean, String],
			default: "off"
		},
		preselect: Boolean,
		type: {
			type: String,
			default: "text"
		},
		value: String
	}
};

/**
 * @example <k-input :value="text" @input="text = $event" name="text" type="text" />
 */
export default {
	mixins: [Input, props],
	data() {
		return {
			listeners: {
				...this.$listeners,
				input: (event) => this.onInput(event.target.value)
			}
		};
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

		if (this.$props.preselect) {
			this.select();
		}
	},
	methods: {
		onInput(value) {
			this.$emit("input", value);
		},
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
