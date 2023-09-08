<template>
	<ul
		:style="'--columns:' + columns"
		class="k-radio-input k-grid"
		data-variant="choices"
	>
		<li v-for="(choice, index) in choices" :key="index">
			<k-choice-input v-bind="choice" @input="$emit('input', choice.value)" />
		</li>
	</ul>
</template>

<script>
import { autofocus, disabled, id, name, required } from "@/mixins/props.js";
import { required as validateRequired } from "vuelidate/lib/validators";

export const props = {
	mixins: [autofocus, disabled, id, name, required],
	props: {
		columns: Number,
		options: {
			default: () => [],
			type: Array
		},
		theme: String,
		value: [String, Number, Boolean]
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	computed: {
		choices() {
			return this.options.map((option, index) => {
				return {
					autofocus: this.autofocus && index === 0,
					checked: this.value === option.value,
					disabled: this.disabled,
					info: option.info,
					label: option.text,
					name: this.name ?? this.id,
					theme: this.theme,
					type: "radio",
					value: option.value
				};
			});
		}
	},
	watch: {
		value: {
			handler() {
				this.validate();
			},
			immediate: true
		}
	},
	methods: {
		focus() {
			this.$el.querySelector("input")?.focus();
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
			value: {
				required: this.required ? validateRequired : true
			}
		};
	}
};
</script>
