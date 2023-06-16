<template>
	<k-drawer
		ref="drawer"
		class="k-form-drawer"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', model)"
	>
		<slot slot="options" name="options" />
		<k-drawer-fields
			:fields="$panel.drawer.tab?.fields"
			:value="model"
			@input="onInput"
			@submit="$emit('submit', model)"
		/>
	</k-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Drawer, Fields],
	emits: ["cancel", "input", "submit"],
	data() {
		return {
			model: this.value
		};
	},
	methods: {
		onInput(value) {
			this.model = value;
			this.$emit("input", this.model);
		}
	}
};
</script>
