<template>
	<k-tabs
		v-if="hasTabs"
		class="k-drawer-tabs"
		:tab="tab"
		:tabs="tabsWithClickHandler"
	/>
</template>

<script>
export const props = {
	props: {
		tab: {
			type: String
		},
		tabs: {
			default: () => ({}),
			type: [Array, Object]
		}
	}
};

/**
 * @displayName DrawerTabs
 * @since 4.0.0
 */
export default {
	mixins: [props],
	emits: ["open"],
	computed: {
		hasTabs() {
			return this.$helper.object.length(this.tabs) > 1;
		},
		tabsWithClickHandler() {
			let tabs = this.tabs;

			if (Array.isArray(tabs) === false) {
				tabs = Object.values(tabs);
			}

			return tabs.map((tab) => ({
				...tab,
				click: () => this.$emit("open", tab.name)
			}));
		}
	}
};
</script>

<style>
.k-drawer-tabs.k-tabs {
	flex-grow: 1;
	gap: 0;
	margin: 0;
	justify-content: end;
}
.k-drawer-tabs .k-tabs-button {
	--button-height: calc(var(--drawer-header-height) - var(--spacing-1));
	--button-padding: var(--spacing-3);
	display: flex;
	align-items: center;
	font-size: var(--text-xs);
	margin-block: 0;
}
.k-drawer-tabs .k-tabs-button[aria-current="true"]::after {
	z-index: var(--z-toolbar);
}
</style>
