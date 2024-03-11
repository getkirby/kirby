<template>
	<k-panel-inside class="k-system-view">
		<k-header>
			{{ $t("view.system") }}
		</k-header>

		<k-section :headline="$t('environment')">
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
		environment: Array,
		exceptions: Array,
		plugins: Array,
		security: Array,
		urls: Object
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
	}
};
</script>

<style>
.k-system-info .k-stat-label {
	color: var(--theme, var(--color-black));
}
</style>
