<template>
	<div class="k-drawer-body scroll-y-auto">
		<slot />
	</div>
</template>

<style>
.k-drawer-body {
	padding: 1.5rem;
	flex-grow: 1;
	background: var(--color-background);
}
/* Sticky elements inside drawer */
.k-drawer-body .k-toolbar:not([data-inline="true"]):has(~ :focus-within),
.k-drawer-body .k-table th {
	top: -1.5rem;
}
</style>
