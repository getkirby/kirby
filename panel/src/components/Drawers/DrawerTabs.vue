<template>
	<nav v-if="hasTabs" class="k-drawer-tabs">
		<k-button
			v-for="tabButton in tabs"
			:key="tabButton.name"
			:current="tab == tabButton.name"
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
