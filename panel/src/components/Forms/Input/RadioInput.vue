<template>
	<fieldset :disabled="disabled" class="k-radio-input">
		<legend class="sr-only">{{ $t("options") }}</legend>

		<ul :style="'--columns:' + columns" class="k-grid" data-variant="choices">
			<li v-for="(choice, index) in choices" :key="index">
				<k-choice-input
					v-bind="choice"
					@click.native.stop="toggle(choice.value)"
					@input="$emit('input', choice.value)"
				/>
			</li>
		</ul>
	</fieldset>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { options } from "@/mixins/props.js";

export const props = {
	mixins: [InputProps, options],
	props: {
		columns: Number,
		reset: {
			default: true,
			type: Boolean
		},
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
					icon: option.icon,
					id: `${this.id}-${index}`,
					info: option.info,
					label: option.text,
					name: this.name ?? this.id,
					required: this.required,
					type: "radio",
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
		toggle(value) {
			if (value === this.value && this.reset && !this.required) {
				this.$emit("input", "");
			}
		}
	}
};
</script>
