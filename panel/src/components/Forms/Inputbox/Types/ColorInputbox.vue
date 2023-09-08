<template>
	<k-inputbox v-bind="$props" type="color">
		<k-inputbox-description slot="before" position="before">
			<button
				:style="!invalid ? 'color: ' + value : null"
				class="k-color-preview"
				type="button"
				@click="$refs.picker.toggle()"
			/>
			<k-dropdown-content
				ref="picker"
				class="k-color-field-picker"
				@open="$nextTick(setPicker)"
			>
				<k-color
					ref="color"
					:alpha="alpha"
					@input="onPicker($event.target.value)"
				/>

				<div class="k-color-field-options">
					<button
						v-for="color in convertedOptions"
						:key="color.value"
						:aria-current="color.value === currentOption?.value"
						:style="'color: ' + color.value"
						:title="color.text ?? color.value"
						type="button"
						class="k-color-preview"
						@click="$refs.input.$refs.input.onPaste(color.value)"
					/>
				</div>
			</k-dropdown-content>
		</k-inputbox-description>
		<k-color-input v-bind="$props" @input="$emit('input', $event)" />
		<k-inputbox-icon slot="icon">
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
import { props as InputProps } from "@/components/Forms/Input/ColorInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		icon: {
			default: "pipette",
			type: String
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"]
};
</script>

<style>
.k-inputbox[data-type="color"] .k-color-input {
	padding: var(--input-padding);
}
</style>
