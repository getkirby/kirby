<template>
	<component :is="component" v-if="component" />
	<k-view-button
		v-else
		:text="$t('add')"
		icon="add"
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

			return null;
		}
	}
};
</script>
