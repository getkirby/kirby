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
		<template #options>
			<slot name="options" />
		</template>

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
import { props as FieldsProps } from "./Elements/Fields.vue";

/**
 * The form drawer can be used to display longer, more complex
 * forms than a form dialog.
 */
export default {
	mixins: [Drawer, FieldsProps],
	emits: ["cancel", "crumb", "input", "submit", "tab"]
};
</script>
