<template>
	<k-tags ref="tags" v-bind="$props" @input="$emit('input', $event)" />
</template>

<script>
import { id, name, required } from "@/mixins/props.js";
import { props as Tags } from "@/components/Navigation/Tags.vue";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength
} from "vuelidate/lib/validators";

export const props = {
	mixins: [id, name, required, Tags],
	props: {
		icon: {
			type: [String, Boolean],
			default: "tag"
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	watch: {
		value: {
			handler() {
				this.$emit("invalid", this.$v.$invalid, this.$v);
			},
			immediate: true
		}
	},
	validations() {
		return {
			value: {
				required: this.required ? validateRequired : true,
				minLength: this.min ? validateMinLength(this.min) : true,
				maxLength: this.max ? validateMaxLength(this.max) : true
			}
		};
	}
};
</script>

<style>
/* Field Theme */
.k-input[data-theme="field"][data-type="tags"] .k-tags {
	padding: 0.25rem;
}
</style>
