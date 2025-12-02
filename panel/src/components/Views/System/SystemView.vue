<template>
	<k-panel-inside class="k-system-view">
		<k-header>
			{{ $t("view.system") }}

			<template #buttons>
				<k-view-buttons :buttons="buttons" />
			</template>
		</k-header>

		<k-section
			:headline="$t('environment')"
			:buttons="[
				{
					text: $t('system.info.copy'),
					icon: 'copy',
					responsive: true,
					click: () => copy()
				}
			]"
		>
			<k-stats :reports="environment" size="medium" class="k-system-info" />
		</k-section>

		<security :security="security" :urls="urls" />
		<plugins :plugins="plugins" />
	</k-panel-inside>
</template>

<script>
import Plugins from "./SystemPlugins.vue";
import Security from "./SystemSecurity.vue";

/**
 * @internal
 */
export default {
	components: {
		Plugins,
		Security
	},
	props: {
		buttons: Array,
		environment: Array,
		exceptions: Array,
		info: Object,
		plugins: Array,
		security: Array,
		urls: [Object, Array]
	},
	mounted() {
		// print exceptions from the backend's update check
		// to console for debugging
		if (this.exceptions.length > 0) {
			console.info(
				"The following errors occurred during the update check of Kirby and/or plugins:"
			);
			this.exceptions.map((exception) => console.warn(exception));
			console.info("End of errors from the update check.");
		}
	},
	methods: {
		copy() {
			const info = JSON.stringify(
				{
					info: this.info,
					security: this.security.map((issue) => issue.text),
					plugins: this.plugins.map((plugin) => ({
						name: plugin.name.text,
						version: plugin.version.currentVersion
					}))
				},
				null,
				2
			);

			this.$helper.clipboard.write(info);
			this.$panel.notification.success({
				message: this.$t("system.info.copied")
			});
		}
	}
};
</script>

<style>
.k-system-info .k-stat-label {
	color: var(--theme-color-text, currentColor);
}
</style>
