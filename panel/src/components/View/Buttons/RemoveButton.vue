<template>
	<component :is="component" v-if="component" />
	<k-button
		v-else
		:text="$t('delete')"
		icon="trash"
		size="sm"
		variant="filled"
		@click="$emit('action', 'remove')"
	/>
</template>

<script>
export default {
	computed: {
		component() {
			const view = this.$panel.view.component.match(/^^k-(.*)-view$/)[1];
			const component = `k-view-remove-${view}-button`;

			if (this.$helper.isComponent(component)) {
				return component;
			}

			return null;
		}
	}
};
</script>
