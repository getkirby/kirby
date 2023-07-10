<template>
	<k-panel class="k-panel-outside" tabindex="0">
		<slot />
	</k-panel>
</template>

<style>
/* TODO: body:has(.k-panel-outside) {
	background: var(--color-gray-250);
} */

.k-panel.k-panel-outside {
	display: grid;
	grid-template-rows: 1fr;
	place-items: center;
	min-height: 100vh;
	min-height: 100dvh;
	padding: var(--spacing-6);
	background: var(--color-gray-250);
}
</style>
