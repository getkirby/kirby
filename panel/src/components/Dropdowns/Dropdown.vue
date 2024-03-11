<template>
	<span class="k-dropdown" @click.stop>
		<slot />
	</span>
</template>

<script>
/**
 * @deprecated 4.0.0 Use `<k-dropdown-content>` as standalone.
 */
export default {
	mounted() {
		window.panel.deprecated(
			"<k-dropdown> will be removed in a future version. Since Kirby 4.0, you don't need it anymore as wrapper element. Use `<k-dropdown-content>` as standalone instead."
		);
	}
};
</script>

<style>
.k-dropdown {
	position: relative;
}
</style>
