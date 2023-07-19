<template>
	<k-drawer
		ref="drawer"
		class="k-form-drawer"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@crumb="$emit('crumb', $event)"
		@submit="$emit('submit', value)"
		@tab="$emit('tab', $event)"
	>
		<slot slot="options" name="options" />
		<k-drawer-fields
			:fields="fields"
			:value="value"
			@input="$emit('input', $event)"
			@submit="$emit('submit', $event)"
		/>
	</k-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Drawer, Fields],
	emits: ["cancel", "crumb", "input", "submit", "tab"]
};
</script>
