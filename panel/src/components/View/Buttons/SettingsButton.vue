<template>
	<div>
		<k-button
			:disabled="$panel.view.isLocked"
			:dropdown="true"
			:title="$t('settings')"
			icon="cog"
			variant="filled"
			size="sm"
			class="k-header-settings-button k-view-options"
			@click="$refs.settings.toggle()"
		/>
		<k-dropdown-content
			ref="settings"
			:options="$dropdown(model.link)"
			align-x="end"
			@action="action"
		/>
	</div>
</template>

<script>
/**
 * Header button to open the model's settings dropdown
 * @since 5.0.0
 */
export default {
	inheritAttrs: false,
	computed: {
		model() {
			return this.$panel.view.props.model;
		}
	},
	methods: {
		action(action) {
			if (this.$panel.view.component === "k-file-view") {
				switch (action) {
					case "replace":
						return this.$panel.upload.replace({
							...this.$panel.view.props.preview,
							...this.model
						});
				}
			}
		}
	}
};
</script>
