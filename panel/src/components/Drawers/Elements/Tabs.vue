<template>
	<nav v-if="hasTabs" class="k-drawer-tabs">
		<k-button
			v-for="tabButton in tabs"
			:key="tabButton.name"
			:current="tab === tabButton.name"
			:text="tabButton.label"
			class="k-drawer-tab"
			@click.stop="$emit('tab', tabButton.name)"
		/>
	</nav>
</template>

<script>
export default {
	props: {
		tab: String,
		tabs: Array
	},
	computed: {
		hasTabs() {
			return this.tabs && Object.keys(this.tabs).length > 1;
		}
	}
};
</script>

<style>
.k-drawer-tabs {
	display: flex;
	align-items: center;
	line-height: 1;
	margin-inline-end: 0.75rem;
}
.k-drawer-tab.k-button {
	height: var(--drawer-header-height);
	padding-inline: 0.75rem;
	display: flex;
	align-items: center;
	font-size: var(--text-xs);
}
.k-drawer-tab.k-button[aria-current]::after {
	position: absolute;
	bottom: -1px;
	inset-inline: 0.75rem;
	content: "";
	background: var(--color-black);
	height: 2px;
	z-index: 1;
}
</style>
