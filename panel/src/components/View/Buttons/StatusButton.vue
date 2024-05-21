<template>
	<k-button
		v-if="$panel.view.component === 'k-page-view' && status"
		v-bind="button"
		:responsive="true"
		:text="status.label"
		size="sm"
		variant="filled"
		class="k-view-status-button k-page-status-button"
		@click="$dialog(model.link + '/changeStatus')"
	/>
</template>

<script>
/**
 * View header button to change the page status
 * @since 5.0.0
 */
export default {
	inheritAttrs: false,
	computed: {
		button() {
			return this.$helper.page.status.call(
				this,
				this.model.status,
				!this.permissions.changeStatus || this.$panel.view.isLocked
			);
		},
		model() {
			return this.$panel.view.props.model;
		},
		permissions() {
			return this.$panel.view.props.permissions;
		},
		status() {
			return this.$panel.view.props.status;
		}
	}
};
</script>
