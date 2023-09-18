<template>
	<ul
		v-if="swatches.length"
		:aria-disabled="disabled"
		class="k-coloroption-input"
	>
		<li v-for="(swatch, index) in swatches" :key="swatch.value">
			<label
				:aria-current="swatch.value === value"
				:style="'color: ' + swatch.value"
				:title="swatch.title"
				class="k-color-preview"
			>
				<input
					:autofocus="index === 0 && autofocus"
					:checked="swatch.value === value"
					:disabled="disabled"
					:name="name ?? id"
					:required="required"
					:value="swatch.value"
					type="radio"
					@input="$emit('input', swatch.value)"
				/>
			</label>
		</li>
	</ul>
</template>

<script>
import { autofocus, disabled, id, name, required } from "@/mixins/props.js";

export const props = {
	mixins: [autofocus, disabled, id, name, required],
	props: {
		format: {
			type: String,
			default: "hex",
			validator: (format) => ["hex", "rgb", "hsl"].includes(format)
		},
		options: {
			default: () => [],
			type: Array
		},
		value: {
			type: String
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	data() {
		return {
			color: null
		};
	},
	computed: {
		swatches() {
			return this.options.map((color) => {
				return {
					...color,
					title: color.text ?? color.value,
					value: this.colorToString(color.value)
				};
			});
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
.k-coloroption-input {
	--color-preview-size: var(--input-height);
	display: grid;
	grid-template-columns: repeat(auto-fill, var(--color-preview-size));
	gap: var(--spacing-2);
}
.k-coloroption-input .k-color-preview[aria-current] {
	outline: var(--outline);
}
.k-coloroption-input[aria-disabled] {
	opacity: var(--opacity-disabled);
}
</style>
