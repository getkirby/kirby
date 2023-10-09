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
		<slot slot="options" name="options" />
		<k-dialog-text :text="text" />
	</k-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import { props as TextProps } from "./Elements/Text.vue";

export default {
	mixins: [Drawer, TextProps],
	emits: ["cancel", "crumb", "input", "submit", "tab"]
};
</script>
