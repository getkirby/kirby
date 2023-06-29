<template>
	<k-panel class="k-panel-outside" tabindex="0">
		<slot />
	</k-panel>
</template>

<style>
.k-panel.k-panel-outside {
	display: grid;
	grid-template-rows: 1fr;
	place-items: center;
	height: 100vh;
	height: 100dvh;
	background: var(--color-gray-200);
	padding: var(--spacing-6);
}
</style>
