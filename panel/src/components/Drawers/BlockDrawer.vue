<template>
	<k-form-drawer
		ref="drawer"
		:expand="isExpanded"
		class="k-block-drawer"
		v-bind="$props"
		@cancel="$emit('cancel', $event)"
		@crumb="$emit('crumb', $event)"
		@input="$emit('input', $event)"
		@submit="$emit('submit', $event)"
		@tab="$emit('tab', $event)"
	>
		<template #options>
			<k-button
				class="k-drawer-option k-drawer-option-expand"
				:icon="isExpanded ? 'collapse' : 'expand'"
				@click.prevent.stop="isExpanded = !isExpanded"
			/>
			<k-button
				v-if="hidden"
				class="k-drawer-option"
				icon="hidden"
				@click="$emit('show')"
			/>
			<k-button
				:disabled="!prev"
				class="k-drawer-option"
				icon="angle-left"
				@click="$emit('prev')"
			/>
			<k-button
				:disabled="!next"
				class="k-drawer-option"
				icon="angle-right"
				@click="$emit('next')"
			/>
			<k-button class="k-drawer-option" icon="trash" @click="$emit('remove')" />
		</template>
	</k-form-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import { props as Fields } from "./Elements/Fields.vue";

export const props = {
	props: {
		isExpanded: {
			type: Boolean
		},
		hidden: {
			type: Boolean
		},
		next: {
			type: Object
		},
		prev: {
			type: Object
		}
	}
};

export default {
	mixins: [Drawer, Fields, props],
	emits: [
		"cancel",
		"crumb",
		"input",
		"next",
		"prev",
		"remove",
		"show",
		"submit",
		"tab"
	]
};
</script>

<style>
@media screen and (max-width: 50rem) {
	.k-button.k-drawer-option-expand {
		display: none;
	}
}
</style>
