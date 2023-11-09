<template>
	<k-tags
		ref="tags"
		v-bind="$props"
		class="k-multiselect-input"
		@input="$emit('input', $event)"
		@click.native.stop="$refs.toggle.$el.click()"
	>
		<k-button
			v-if="!max || value.length < max"
			:id="id"
			ref="toggle"
			:autofocus="autofocus"
			:disabled="disabled"
			class="k-multiselect-input-toggle k-tags-navigatable"
			size="xs"
			icon="triangle-down"
			@click="$refs.dropdown.open()"
			@keydown.native.delete="$refs.tags.focus('prev')"
			@keydown.native="toggle"
		/>

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
		toggle(event) {
			if (event.metaKey || event.altKey || event.ctrlKey) {
				return false;
			}

			if (event.key === "ArrowDown") {
				this.$refs.create.open();
				event.preventDefault();
				return;
			}

			if (String.fromCharCode(event.keyCode).match(/(\w)/g)) {
				this.$refs.create.open();
			}
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
	--button-color-back: var(--color-gray-250);
	--button-rounded: var(--rounded-sm);
}
</style>
