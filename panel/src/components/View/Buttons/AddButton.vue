<template>
	<component v-if="component" :is="component" />
	<k-button
		v-else
		:text="$t('add')"
		icon="add"
		size="sm"
		variant="filled"
		@click="$emit('action', 'add')"
	/>
</template>

<script>
export default {
	computed: {
		component() {
			const view = this.$panel.view.component.match(/^^k-(.*)-view$/)[1];
			const component = `k-view-add-${view}-button`;

			if (this.$helper.isComponent(component)) {
				return component;
			}
		}
	}
};
</script>
