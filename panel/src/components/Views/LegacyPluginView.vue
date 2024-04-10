<template>
	<k-panel-inside>
		<component :is="'k-' + id + '-plugin-view'" />
	</k-panel-inside>
</template>

<script>
/**
 * @deprecated Will be removed in v5.0.0
 * @todo Remove in v5.0.0
 */
export default {
	props: {
		id: String
	},
	mounted() {
		window.panel.deprecated(
			"<k-plugin-view> will be removed in a future version."
		);
	}
};
</script>
