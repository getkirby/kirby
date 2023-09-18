<template>
	<k-inputbox v-bind="$props" type="color">
		<k-inputbox-description slot="before" position="before">
			<button
				:style="!invalid ? 'color: ' + value : null"
				class="k-color-preview"
				type="button"
				@click="$refs.picker.toggle()"
			/>
			<k-dropdown-content ref="picker">
				<k-colorpicker-input
					ref="color"
					:alpha="alpha"
					:format="format"
					:options="convertedOptions"
					:value="value"
					@input="$emit('input', $event)"
				/>
			</k-dropdown-content>
		</k-inputbox-description>
		<k-colorname-input v-bind="$props" @input="$emit('input', $event)" />

		<k-inputbox-description v-if="currentOption?.text" position="after" #after>
			{{ currentOption.text }}
		</k-inputbox-description>

		<k-inputbox-icon #icon>
			<k-button
				:disabled="disabled"
				:icon="icon"
				:title="$t('open')"
				class="k-inputbox-icon-button"
				@click="$refs.picker.toggle()"
			/>
		</k-inputbox-icon>
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/ColornameInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		icon: {
			default: "pipette",
			type: String
		},
		options: {
			default: () => [],
			type: Array
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"],
	computed: {
		convertedOptions() {
			return this.options.map((option) => ({
				...option,
				value: this.convert(option.value)
			}));
		},
		currentOption() {
			return this.convertedOptions.find((color) => color.value === this.value);
		}
	},
	methods: {
		convert(value) {
			return this.$library.colors.toString(value, this.format, this.alpha);
		}
	}
};
</script>

<style>
.k-color-inputbox .k-inputbox-before {
	padding-inline-start: 0.375rem;
}
.k-color-inputbox .k-colorname-input {
	padding: var(--input-padding);
}
.k-color-inputbox .k-colorpicker-input {
	padding: var(--spacing-1);
}
</style>
