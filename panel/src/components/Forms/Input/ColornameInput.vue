<template>
	<input
		ref="input"
		v-bind="{
			autofocus,
			disabled,
			id,
			name,
			placeholder,
			required,
			value
		}"
		autocomplete="off"
		spellcheck="false"
		type="text"
		class="k-colorname-input"
		@blur="onBlur"
		@input="onInput($event.target.value)"
		@paste="onPaste"
		@keydown.meta.s.stop.prevent="onSave"
	/>
</template>

<script>
import { autofocus, disabled, id, name, required } from "@/mixins/props.js";

import { required as validateRequired } from "vuelidate/lib/validators";

export const props = {
	mixins: [autofocus, disabled, id, name, required],
	props: {
		alpha: {
			type: Boolean,
			default: true
		},
		/**
		 * @values `hex`, `rgb`, `hsl`
		 */
		format: {
			type: String,
			default: "hex",
			validator: (format) => ["hex", "rgb", "hsl"].includes(format)
		},
		placeholder: String,
		value: String
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	data() {
		return {
			color: null
		};
	},
	watch: {
		value() {
			this.onInvalid();
		}
	},
	mounted() {
		this.onInvalid();
		this.onBlur();

		if (this.$props.autofocus) {
			this.focus();
		}
	},
	methods: {
		convert(value) {
			if (!value) {
				return value;
			}

			// create a new secret tester
			const test = document.createElement("div");

			// set the text color
			test.style.color = value;

			// it has to be in the document to work
			document.body.append(test);

			// check the computed style for a usable rgb value
			value = window.getComputedStyle(test).color;

			// remove the element
			test.remove();

			try {
				return this.$library.colors.toString(value, this.format, this.alpha);
			} catch (e) {
				return value;
			}
		},
		focus() {
			this.$refs.input.focus();
		},
		onBlur() {
			const value = this.convert(this.value);
			this.onInput(value);
		},
		onInput(value) {
			this.$emit("input", value);
		},
		onInvalid() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
		},
		onPaste(input) {
			if (input instanceof ClipboardEvent) {
				input = this.$helper.clipboard.read(input, true);
			}

			const value = this.convert(input);
			this.onInput(value);
		},
		onSave() {
			this.onBlur();
			this.$emit("submit");
		},
		select() {
			this.$refs.input.select();
		}
	},
	validations() {
		return {
			value: {
				color: (value) =>
					value ? this.$library.colors.parse(value) !== null : true,
				required: this.required ? validateRequired : true
			}
		};
	}
};
</script>

<style>
.k-colorname-input {
	font-family: var(--font-mono);
}
</style>
