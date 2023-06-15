<template>
	<k-drawer
		ref="drawer"
		class="k-form-drawer"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', value)"
	>
		<slot slot="options" name="options" />
		<k-drawer-fields
			:fields="$panel.drawer.tab?.fields"
			:value="value"
			@input="$emit('input', $event)"
			@submit="$emit('submit', value)"
		/>
	</k-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Drawer, Fields],
	emits: ["cancel", "input", "submit"]
};
</script>
