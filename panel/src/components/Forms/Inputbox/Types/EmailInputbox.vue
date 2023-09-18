<template>
	<k-inputbox v-bind="$props" type="email">
		<k-email-input v-bind="$props" @input="$emit('input', $event)" />
		<k-inputbox-icon slot="icon">
			<k-button
				:disabled="disabled || !value?.length"
				:link="`mailto:${value}`"
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
import { props as InputProps } from "@/components/Forms/Input/EmailInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		icon: {
			default: "email",
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
.k-email-inputbox .k-email-input {
	padding: var(--input-padding);
}
</style>
