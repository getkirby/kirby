<template>
	<k-inside :class="`k-iframe-view k-view-name-${$view.id}`">
		<k-view v-if="url">
			<iframe :src="url" scrolling="no" frameborder="0" loading="lazy" />
		</k-view>
		<k-error-view v-else :error="error" />
	</k-inside>
</template>

<script>
export default {
	props: {
		error: String,
		url: String
	}
};
</script>

<style>
.k-iframe-view .k-panel-view {
	padding-bottom: 0;
}
.k-iframe-view iframe {
	display: block;
	width: 100%;
	height: calc(100vh - 2.5rem);
}
</style>
