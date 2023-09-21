<template>
	<k-inputbox v-bind="$props" type="search">
		<k-search-input v-bind="$props" @input="$emit('input', $event)" />
		<k-inputbox-icon v-if="value?.length" slot="icon">
			<k-button
				class="k-inputbox-icon-button"
				icon="cancel"
				@click="$emit('input', '')"
			/>
		</k-inputbox-icon>
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/SearchInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		icon: {
			default: "search",
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
.k-search-inputbox {
	--inputbox-color-back: var(--color-gray-250);
}
</style>
