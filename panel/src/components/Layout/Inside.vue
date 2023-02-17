<template>
	<k-panel class="k-panel-inside" tabindex="0">
		<header class="k-panel-header">
			<k-topbar
				:breadcrumb="$view.breadcrumb"
				:license="$license"
				:menu="$menu"
				:view="$view"
			/>
		</header>
		<main class="k-panel-view">
			<slot />
		</main>
		<slot name="footer" />
	</k-panel>
</template>

<script>
export default {
	inheritAttrs: false
};
</script>

<style>
.k-panel-inside:focus {
	outline: 0;
}
</style>
