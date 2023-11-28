<template>
	<k-panel class="k-panel-outside" tabindex="0">
		<!-- @slot Main content for the view  -->
		<slot />
	</k-panel>
</template>

<script>
/**
 * Wrapper for views for signed-out users.
 * @displayName PanelOutside
 */
export default {};
</script>

<style>
.k-panel.k-panel-outside {
	display: grid;
	grid-template-rows: 1fr;
	place-items: center;
	min-height: 100vh;
	min-height: 100dvh;
	padding: var(--spacing-6);
}
</style>
