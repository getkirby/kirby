<template>
	<span class="k-loader">
		<k-icon class="k-loader-icon" type="loader" />
	</span>
</template>

<script>
/**
 * @deprecated 4.0.0 Use `<k-icon type="loader" />` instead
 */
export default {
	mounted() {
		window.panel.deprecated(
			'<k-loader> will be removed in a future version. Use <k-icon type="loader" /> instead.'
		);
	}
};
</script>

<style>
.k-loader {
	z-index: 1;
}
.k-loader-icon {
	animation: Spin 0.9s linear infinite;
}
</style>
