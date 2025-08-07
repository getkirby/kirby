<template>
	<k-drawer
		ref="drawer"
		class="k-text-drawer"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@crumb="$emit('crumb', $event)"
		@submit="$emit('submit', value)"
		@tab="$emit('tab', $event)"
	>
		<template v-if="$slots.options" #options>
			<slot name="options" />
		</template>

		<k-dialog-text :text="text" />
	</k-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import { props as TextProps } from "./Elements/Text.vue";

/**
 * The text drawer is perfect if you want to display
 * additional information in a longer form that would not
 * fit into a text dialog.
 * @since 4.0.0
 */
export default {
	mixins: [Drawer, TextProps],
	emits: ["cancel", "crumb", "input", "submit", "tab"]
};
</script>
