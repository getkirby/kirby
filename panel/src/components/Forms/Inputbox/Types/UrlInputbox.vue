<template>
	<k-inputbox v-bind="$props" type="url">
		<k-url-input v-bind="$props" @input="$emit('input', $event)" />
		<k-inputbox-icon slot="icon">
			<k-button
				:disabled="disabled || !value?.length"
				:link="value"
				:icon="icon"
				:title="$t('open')"
				target="_blank"
				class="k-inputbox-icon-button"
			/>
		</k-inputbox-icon>
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/UrlInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		icon: {
			default: "url",
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
.k-url-inputbox .k-url-input {
	padding: var(--input-padding);
}
</style>
