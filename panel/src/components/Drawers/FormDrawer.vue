<template>
	<k-drawer
		ref="drawer"
		v-bind="$props"
		class="k-form-drawer"
		@cancel="cancel"
		@submit="submit"
	>
		<slot slot="options" name="options" />
		<k-drawer-fields
			:fields="$panel.drawer.tab?.fields"
			:value="value"
			@input="input"
			@submit="submit"
		/>
	</k-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Drawer, Fields]
};
</script>
