<template>
	<k-tags
		ref="tags"
		:draggable="false"
		:options="options"
		:sort="true"
		:value="value"
		class="k-multiselect-input"
		@input="$emit('input', $event)"
		@click.native.stop="$refs.dropdown.toggle()"
	>
		<k-picklist-dropdown
			ref="dropdown"
			v-bind="$props"
			:options="options"
			@input="$emit('input', $event)"
		/>
	</k-tags>
</template>

<script>
import Input from "@/mixins/input.js";
import { picklist as PicklistInput } from "@/components/Forms/Input/PicklistInput.vue";
import { name, required } from "@/mixins/props.js";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength
} from "vuelidate/lib/validators";

export const props = {
	mixins: [name, required, PicklistInput],
	props: {
		value: {
			default: () => [],
			type: Array
		}
	},
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

export default {
	mixins: [Input, props]
};
</script>

<style>
.k-multiselect-input {
	padding: var(--tags-gap);
}
</style>
