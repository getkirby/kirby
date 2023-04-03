<template>
	<k-drawer
		ref="drawer"
		:autofocus="autofocus"
		:id="id"
		:icon="icon"
		:loading="loading"
		:tab="currentTab"
		:tabs="tabs"
		:title="title"
		:visible="visible"
		class="k-form-drawer"
	>
		<template #options>
			<slot name="options" />
		</template>
		<template #default>
			<k-drawer-fields
				:fields="currentFields"
				:value="model"
				@input="input"
				@invalid="invalid"
				@submit="submit"
			/>
		</template>
	</k-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Drawer, Fields],
	data() {
		return {
			currentFields: null,
			currentTab: null,

			// Since fiber drawers don't update their `value` prop
			// on an emitted `input` event, we need to ensure a local
			// state of all updated values
			model: this.value
		};
	},
	watch: {
		value(value) {
			this.model = value;
		}
	},
	methods: {
		input(value) {
			this.model = value;
			this.$emit("input", this.model);
		},
		invalid() {
			this.$emit("invalid", this.model);
		},
		open(tabId, focus = true) {
			this.openTab(this.tabs[tabId]);
			this.$refs.drawer.open();
			this.$refs.drawer.focus();
		},
		openTab(tab) {
			this.currentTab = tab?.id ?? Object.keys(this.tabs)[0];
			this.currentFields = this.tabs[this.currentTab]?.fields ?? {};
		},
		submit() {
			this.$emit("submit", this.model);
		}
	}
};
</script>
