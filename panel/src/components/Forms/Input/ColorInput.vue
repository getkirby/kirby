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
		class="k-text-input k-color-input"
		@blur="onBlur"
		@input="onInput($event.target.value)"
		@paste="onPaste"
		@keydown.meta.s.stop.prevent="onSave"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { placeholder } from "@/mixins/props.js";
import { required as validateRequired } from "vuelidate/lib/validators";

export const props = {
	mixins: [InputProps, placeholder],
	props: {
		alpha: {
			type: Boolean,
			default: true
		},
		/**
		 * @values "hex", "rgb", "hsl"
		 */
		format: {
			type: String,
			default: "hex",
			validator: (format) => ["hex", "rgb", "hsl"].includes(format)
		},
		value: String
	}
};

/**
 * @since 4.0.0
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
		this.onBlur();

		if (this.$props.autofocus) {
			this.focus();
		}
	},
	methods: {
		convert(value) {
			try {
				return this.$library.colors.toString(value, this.format, this.alpha);
			} catch (e) {
				return value;
			}
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
.k-color-input {
	font-family: var(--font-mono);
}
</style>
