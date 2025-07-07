<template>
	<fieldset
		v-if="choices.length"
		:disabled="disabled"
		class="k-coloroptions-input"
	>
		<legend class="sr-only">{{ $t("options") }}</legend>
		<ul>
			<li v-for="(choice, index) in choices" :key="index">
				<label :title="choice.title">
					<input
						:autofocus="autofocus && index === 0"
						:checked="choice.value === value"
						:disabled="disabled"
						:formnovalidate="novalidate"
						:name="name ?? id"
						:required="required"
						:value="choice.value"
						class="input-hidden"
						type="radio"
						@click="toggle(choice.value)"
						@input="$emit('input', choice.value)"
					/>
					<k-color-frame :color="choice.value" />
				</label>
			</li>
		</ul>
	</fieldset>
</template>

<script>
import RadioInput, { props as RadioInputProps } from "./RadioInput.vue";

export const props = {
	mixins: [RadioInputProps],
	props: {
		/**
		 * @values "hex", "rgb", "hsl"
		 */
		format: {
			type: String,
			default: "hex",
			validator: (format) => ["hex", "rgb", "hsl"].includes(format)
		},
		value: {
			type: String
		}
	}
};

/**
 * @since 4.0.0
 * @example <k-coloroptions-input :options="options" :value="value" @input="value = $event" />
 */
export default {
	mixins: [RadioInput, props],
	computed: {
		choices() {
			return this.options.map((color) => ({
				...color,
				title: color.text ?? color.value,
				value: this.colorToString(color.value)
			}));
		}
	},
	methods: {
		colorToString(value) {
			try {
				return this.$library.colors.toString(value, this.format);
			} catch {
				return value;
			}
		}
	}
};
</script>

<style>
.k-coloroptions-input {
	--color-preview-size: var(--input-height);
}
.k-coloroptions-input ul {
	display: grid;
	grid-template-columns: repeat(auto-fill, var(--color-preview-size));
	gap: var(--spacing-2);
}

.k-coloroptions-input input:focus + .k-color-frame {
	outline: var(--outline);
}
.k-coloroptions-input[disabled] label {
	opacity: var(--opacity-disabled);
	cursor: not-allowed;
}
.k-coloroptions-input input:checked + .k-color-frame {
	outline: 1px solid var(--color-gray-600);
	outline-offset: 2px;
}
</style>
