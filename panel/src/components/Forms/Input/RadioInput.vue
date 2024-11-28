<template>
	<ul
		:style="{ '--columns': columns }"
		class="k-radio-input k-grid"
		data-variant="choices"
	>
		<li v-for="(choice, index) in choices" :key="index">
			<k-choice-input
				v-bind="choice"
				@click.native.stop="toggle(choice.value)"
				@input="$emit('input', choice.value)"
			/>
		</li>
	</ul>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { options } from "@/mixins/props.js";
import { required as validateRequired } from "vuelidate/lib/validators";

export const props = {
	mixins: [InputProps, options],
	props: {
		columns: {
			default: 1,
			type: Number
		},
		reset: {
			default: true,
			type: Boolean
		},
		theme: String,
		value: [String, Number, Boolean]
	}
};

export default {
	mixins: [Input, props],
	computed: {
		choices() {
			return this.options.map((option, index) => {
				return {
					autofocus: this.autofocus && index === 0,
					checked: this.value === option.value,
					disabled: this.disabled || option.disabled,
					id: `${this.id}-${index}`,
					info: option.info,
					label: option.text,
					name: this.name ?? this.id,
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
		toggle(value) {
			if (value === this.value && this.reset && !this.required) {
				this.$emit("input", "");
			}
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
