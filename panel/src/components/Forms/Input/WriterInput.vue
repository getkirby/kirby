<template>
	<k-writer
		ref="input"
		v-bind="$props"
		class="k-writer-input"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { maxlength, minlength } from "@/mixins/props.js";
import { props as WriterProps } from "@/components/Forms/Writer/Writer.vue";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength
} from "vuelidate/lib/validators";

export const props = {
	mixins: [InputProps, WriterProps, maxlength, minlength],
	computed: {
		counterValue() {
			const plain = this.$helper.string.stripHTML(this.value);
			return this.$helper.string.unescapeHTML(plain);
		}
	}
};

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
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		onInvalid() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
		}
	},
	validations() {
		return {
			counterValue: {
				required: this.required ? validateRequired : true,
				minLength: this.minlength ? validateMinLength(this.minlength) : true,
				maxLength: this.maxlength ? validateMaxLength(this.maxlength) : true
			}
		};
	}
};
</script>
