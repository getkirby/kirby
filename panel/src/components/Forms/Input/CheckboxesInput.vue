<template>
	<fieldset
		:disabled="disabled"
		:class="['k-checkboxes-input', $attrs.class]"
		:style="$attrs.style"
	>
		<legend class="sr-only">{{ $t("options") }}</legend>

		<k-input-validator
			v-bind="{ min, max, required }"
			:value="JSON.stringify(selected)"
		>
			<ul
				:style="{ '--columns': columns }"
				class="k-grid"
				data-variant="choices"
			>
				<li v-for="(choice, index) in choices" :key="index">
					<k-choice-input
						v-bind="choice"
						@input="input(choice.value, $event)"
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
				const checked = this.selected.includes(option.value);

				return {
					autofocus: this.autofocus && index === 0,
					checked: checked,
					disabled:
						this.disabled || option.disabled || (!checked && this.isFull),
					id: `${this.id}-${index}`,
					info: option.info,
					label: option.text,
					name: this.name ?? this.id,
					type: "checkbox",
					value: option.value
				};
			});
		},
		isFull() {
			return this.max && this.selected.length >= this.max;
		}
	},
	watch: {
		value: {
			handler(value) {
				this.selected = Array.isArray(value) ? value : [];
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
		}
	}
};
</script>
