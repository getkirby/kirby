<template>
	<div>
		<component
			:is="drawer.component"
			v-for="drawer in $panel.drawer.history.milestones"
			:key="drawer.id"
			v-bind="isCurrent(drawer.id) ? $panel.drawer.props : drawer.props"
			:breadcrumb="$panel.drawer.breadcrumb"
			:disabled="isCurrent(drawer.id) === false"
			:visible="true"
			v-on="isCurrent(drawer.id) ? $panel.drawer.listeners() : drawer.on"
		/>
	</div>
</template>

<script>
export default {
	methods: {
		isCurrent(id) {
			return this.$panel.drawer.id === id;
		}
	}
};
</script>

<style>
.k-drawer[aria-disabled="true"] {
	display: none;
	pointer-events: none;
}
</style>
