<template>
	<k-form-drawer
		ref="drawer"
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
				v-if="hidden"
				class="k-drawer-option"
				icon="hidden"
				@click="show"
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
import { props as FieldsProps } from "./Elements/Fields.vue";

export const props = {
	props: {
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
	mixins: [Drawer, FieldsProps, props],
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
	],
	methods: {
		show() {
			this.hidden = false;
			this.$emit("show");
		}
	}
};
</script>
