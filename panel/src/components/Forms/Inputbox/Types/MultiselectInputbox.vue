<template>
	<k-inputbox
		v-if="hasOptions"
		v-bind="$props"
		type="multiselect"
		@click.native="$refs.input.focus()"
	>
		<k-multiselect-input
			ref="input"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-inputbox>
	<k-empty v-else :icon="icon" :text="$t('options.none')" />
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/MultiselectInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		icon: {
			default: "checklist",
			type: String
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"],
	computed: {
		hasOptions() {
			return this.options.length > 0 || this.accept !== "options";
		}
	}
};
</script>

<style>
.k-multiselect-inputbox .k-multiselect-input {
	padding: var(--tags-input-gap);
}
</style>
