<template>
	<nav v-if="hasTabs" class="k-drawer-tabs">
		<k-button
			v-for="tabButton in tabs"
			:key="tabButton.name"
			:current="tab === tabButton.name"
			:text="tabButton.label"
			class="k-drawer-tab"
			@click="$emit('open', tabButton.name)"
		/>
	</nav>
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
		}
	}
};
</script>

<style>
.k-drawer-tabs {
	display: flex;
	align-items: center;
	line-height: 1;
}
.k-drawer-tab.k-button {
	--button-height: calc(var(--drawer-header-height) - var(--spacing-1));
	--button-padding: var(--spacing-3);
	display: flex;
	align-items: center;
	font-size: var(--text-xs);
	overflow-x: visible;
}
.k-drawer-tab.k-button[aria-current="true"]::after {
	position: absolute;
	bottom: -2px;
	inset-inline: var(--button-padding);
	content: "";
	background: var(--color-black);
	height: 2px;
	z-index: 1;
}
</style>
