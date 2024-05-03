<template>
	<k-button
		v-if="status"
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
 * Header button to change the page status
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
	},
	mounted() {
		if (this.$panel.view.component !== "k-page-view") {
			console.error(
				"The status view button should only be used for the page view."
			);
		}
	}
};
</script>
