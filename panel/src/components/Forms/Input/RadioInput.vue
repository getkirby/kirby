<template>
	<fieldset
		:class="['k-radio-input', $attrs.class]"
		:disabled="disabled"
		:style="$attrs.style"
	>
		<legend class="sr-only">{{ $t("options") }}</legend>

		<k-input-validator :required="required" :value="JSON.stringify(value)">
			<ul
				:style="{ '--columns': columns }"
				class="k-grid"
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
		</k-input-validator>
	</fieldset>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { options } from "@/mixins/props.js";

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
		}
	}
};
</script>
