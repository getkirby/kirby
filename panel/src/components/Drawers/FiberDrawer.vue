<template>
	<div>
		<component
			:is="drawer.component"
			v-for="drawer in $panel.drawer.history.milestones"
			:key="drawer.id"
			v-bind="isCurrent(drawer.id) ? $panel.drawer.props : drawer.props"
			:breadcrumb="$panel.drawer.breadcrumb"
			:current="isCurrent(drawer.id) === true"
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
