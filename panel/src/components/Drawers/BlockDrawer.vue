<template>
	<k-form-drawer
		ref="drawer"
		class="k-block-drawer"
		v-bind="$props"
		v-on="$listeners"
	>
		<template #options>
			<k-button
				v-if="hidden"
				class="k-drawer-option"
				icon="hidden"
				@click="$panel.drawer.emit('show')"
			/>
			<k-button
				:disabled="!prev"
				class="k-drawer-option"
				icon="angle-left"
				@click="$panel.drawer.emit('prev')"
			/>
			<k-button
				:disabled="!next"
				class="k-drawer-option"
				icon="angle-right"
				@click="$panel.drawer.emit('next')"
			/>
			<k-button
				class="k-drawer-option"
				icon="trash"
				@click="$panel.drawer.emit('remove')"
			/>
		</template>
	</k-form-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import { props as Fields } from "./Elements/Fields.vue";

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
	mixins: [Drawer, Fields, props]
};
</script>
