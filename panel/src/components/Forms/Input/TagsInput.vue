<template>
	<div class="k-tags-input">
		<k-tags
			ref="tags"
			v-bind="$props"
			@input="$emit('input', $event)"
			@click.native.stop
		/>
	</div>
</template>

<script>
import Input from "@/mixins/input.js";
import { name, required } from "@/mixins/props.js";
import { props as TagsProps } from "@/components/Navigation/Tags.vue";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength
} from "vuelidate/lib/validators";

export const props = {
	mixins: [name, required, TagsProps]
};

export default {
	mixins: [Input, props],
	watch: {
		value: {
			handler() {
				this.$emit("invalid", this.$v.$invalid, this.$v);
			},
			immediate: true
		}
	},
	methods: {
		focus() {
			this.$refs.tags.open();
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
.k-tags-input {
	padding: var(--tags-gap);
}
</style>
