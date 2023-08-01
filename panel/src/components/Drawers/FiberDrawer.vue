<template>
	<div>
		<component
			:is="drawer.component"
			v-for="drawer in $panel.drawer.history.milestones"
			:key="drawer.id"
			:breadcrumb="$panel.drawer.breadcrumb"
			:disabled="isCurrent(drawer.id) === false"
			:visible="true"
			v-bind="drawer.props"
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
.k-drawer[aria-disabled] {
	display: none;
	pointer-events: none;
}
</style>
