<template>
	<ul
		:style="{ '--columns': columns }"
		class="k-checkboxes-input k-grid"
		data-variant="choices"
	>
		<li v-for="(choice, index) in choices" :key="index">
			<k-choice-input v-bind="choice" @input="input(choice.value, $event)" />
		</li>
	</ul>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { options } from "@/mixins/props.js";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength
} from "vuelidate/lib/validators";

export const props = {
	mixins: [InputProps, options],
	props: {
		columns: {
			default: 1,
			type: Number
		},
		max: Number,
		min: Number,
		theme: String,
		/**
		 * The value for the input should be provided as array. Each value in the array corresponds with the value in the options. If you provide a string, the string will be split by comma.
		 */
		value: {
			type: Array,
			default: () => []
		}
	}
};

export default {
	mixins: [Input, props],
	data() {
		return {
			selected: []
		};
	},
	computed: {
		choices() {
			return this.options.map((option, index) => {
				return {
					autofocus: this.autofocus && index === 0,
					checked: this.selected.includes(option.value),
					disabled: this.disabled || option.disabled,
					id: `${this.id}-${index}`,
					info: option.info,
					label: option.text,
					name: this.name ?? this.id,
					type: "checkbox",
					value: option.value
				};
			});
		}
	},
	watch: {
		value: {
			handler(value) {
				this.selected = Array.isArray(value) ? value : [];
				this.validate();
			},
			immediate: true
		}
	},
	methods: {
		focus() {
			this.$el.querySelector("input")?.focus();
		},
		input(key, value) {
			if (value === true) {
				this.selected.push(key);
			} else {
				const index = this.selected.indexOf(key);
				if (index !== -1) {
					this.selected.splice(index, 1);
				}
			}
			this.$emit("input", this.selected);
		},
		select() {
			this.focus();
		},
		validate() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
		}
	},
	validations() {
		return {
			selected: {
				required: this.required ? validateRequired : true,
				min: this.min ? validateMinLength(this.min) : true,
				max: this.max ? validateMaxLength(this.max) : true
			}
		};
	}
};
</script>
