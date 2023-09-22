<template>
	<k-inputbox v-bind="$props" type="tel">
		<k-tel-input v-bind="$props" @input="$emit('input', $event)" />
		<k-inputbox-icon v-if="value?.length > 0 && link === true" slot="icon">
			<k-button
				:disabled="disabled"
				:link="`tel:${value}`"
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
import { props as InputProps } from "@/components/Forms/Input/TelInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		icon: {
			default: "phone",
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
