<template>
	<fieldset :disabled="disabled" class="k-checkboxes-input">
		<legend class="sr-only">{{ $t("options") }}</legend>

		<k-array-input
			ref="input"
			v-bind="{
				min,
				max,
				name,
				required,
				value: JSON.stringify(value)
			}"
		>
			<ul :style="'--columns:' + columns" class="k-grid" data-variant="choices">
				<li v-for="(choice, index) in choices" :key="index">
					<k-choice-input
						v-bind="choice"
						@input="input(choice.value, $event)"
					/>
				</li>
			</ul>
		</k-array-input>
	</fieldset>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { options } from "@/mixins/props.js";

export const props = {
	mixins: [InputProps, options],
	props: {
		columns: Number,
		max: Number,
		min: Number,
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
	computed: {
		choices() {
			return this.options.map((option, index) => {
				return {
					autofocus: this.autofocus && index === 0,
					checked: this.value?.includes(option.value),
					disabled: this.disabled || option.disabled,
					id: `${this.id}-${index}`,
					info: option.info,
					label: option.text,
					type: "checkbox",
					value: option.value
				};
			});
		}
	},
	methods: {
		focus() {
			(
				this.$el.querySelector("input:checked") ??
				this.$el.querySelector("input")
			)?.focus();
		},
		input(value, checked) {
			if (checked === true) {
				this.$refs.input.add(value);
			} else {
				this.$refs.input.remove(value);
			}

			this.$emit("input", this.$refs.input.selected);
		}
	}
};
</script>
