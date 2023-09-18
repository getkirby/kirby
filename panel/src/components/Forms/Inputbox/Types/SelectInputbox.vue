<template>
	<k-inputbox
		v-if="options.length"
		v-bind="$props"
		type="select"
		@click.native="$refs.input.focus()"
	>
		<k-select-input
			ref="input"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-inputbox>
	<k-empty v-else :icon="icon" :text="$t('options.none')" />
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/SelectInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps]
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"]
};
</script>
