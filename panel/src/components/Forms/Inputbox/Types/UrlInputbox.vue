<template>
	<k-inputbox v-bind="$props" type="url">
		<k-url-input v-bind="$props" @input="$emit('input', $event)" />
		<k-inputbox-icon v-if="value?.length > 0 && link === true" slot="icon">
			<k-button
				:disabled="disabled"
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
.k-url-inputbox .k-url-input {
	padding: var(--input-padding);
}
</style>
