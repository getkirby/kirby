<template>
	<k-inputbox v-bind="$props" type="email">
		<k-email-input v-bind="$props" @input="$emit('input', $event)" />
		<k-inputbox-icon v-if="value?.length > 0 && link === true" slot="icon">
			<k-button
				:disabled="disabled"
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
		},
		link: {
			type: Boolean,
			default: true
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
	padding: var(--inputbox-padding);
}
</style>
