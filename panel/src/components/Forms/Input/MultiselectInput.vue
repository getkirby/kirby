<template>
	<div class="k-multiselect-input">
		<k-tags
			ref="tags"
			v-bind="$props"
			@input="$emit('input', $event)"
			@click.native.stop="open"
		>
			<k-button
				v-if="!max || value.length < max"
				:id="id"
				ref="toggle"
				:autofocus="autofocus"
				:disabled="disabled"
				class="k-multiselect-input-toggle k-tags-navigatable"
				size="xs"
				icon="angle-down"
				@keydown.native.delete="$refs.tags.focus('prev')"
				@focus.native="open"
			/>
		</k-tags>
		<k-picklist-dropdown
			ref="dropdown"
			v-bind="$props"
			:options="options"
			@input="$emit('input', $event)"
		/>
	</div>
</template>

<script>
import Input from "@/mixins/input.js";
import { picklist as PicklistInputProps } from "@/components/Forms/Input/PicklistInput.vue";
import { props as TagsProps } from "@/components/Navigation/Tags.vue";

import { name, required } from "@/mixins/props.js";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength
} from "vuelidate/lib/validators";

export const props = {
	mixins: [name, required, TagsProps, PicklistInputProps],
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
	},
	methods: {
		open() {
			this.$refs.dropdown.open(this.$el);
		}
	}
};

export default {
	mixins: [Input, props]
};
</script>

<style>
.k-multiselect-input {
	padding: var(--tags-gap);
	cursor: pointer;
}

.k-multiselect-input-toggle.k-button {
	opacity: 0;
}
</style>
