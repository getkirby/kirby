<template>
	<k-drawer
		:id="id"
		ref="drawer"
		:icon="icon"
		:tabs="tabs"
		:tab="tab"
		:title="title"
		class="k-form-drawer"
		@close="$emit('close')"
		@open="$emit('open')"
		@tab="tab = $event"
	>
		<template #options>
			<slot name="options" />
		</template>
		<template #default>
			<k-drawer-fields
				:fields="fields"
				:value="$helper.clone(value)"
				@input="$emit('input', $event)"
				@invalid="$emit('invalid', $event)"
			/>
		</template>
	</k-drawer>
</template>

<script>
import { props as Drawer } from "./Drawer.vue";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Drawer, Fields],
	props: {
		type: String
	},
	data() {
		return {
			tab: null
		};
	},
	computed: {
		fields() {
			const tabId = this.tab || null;
			const tabs = this.tabs;
			const tab = tabs[tabId] || this.firstTab;
			const fields = tab.fields || {};

			return fields;
		},
		firstTab() {
			return Object.values(this.tabs)[0];
		}
	},
	methods: {
		close() {
			this.$refs.drawer.close();
		},
		focus(name) {
			if (typeof this.$refs.form?.focus === "function") {
				this.$refs.form.focus(name);
			}
		},
		open(tab, focus = true) {
			this.$refs.drawer.open();
			this.tab = tab || this.firstTab.name;

			if (focus === true) {
				focus = Object.values(this.fields).find(
					(field) => field.autofocus === true
				)?.name;
			}

			setTimeout(() => {
				this.focus(focus);
			}, 10);
		}
	}
};
</script>
