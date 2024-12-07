<template>
	<div class="k-drawer-body scroll-y-auto">
		<slot />
	</div>
</template>

<script>
/**
 * @displayName DrawerBody
 * @since 4.0.0
 */
export default {};
</script>

<style>
.k-drawer-body {
	padding: var(--drawer-body-padding);
	flex-grow: 1;
	background: var(--drawer-color-back);
}
/* Sticky elements inside drawer */
.k-drawer-body
	.k-writer-input:focus-within
	.k-toolbar:not([data-inline="true"]),
.k-drawer-body .k-textarea-input-wrapper:focus-within .k-toolbar,
.k-drawer-body .k-table th {
	top: -1.5rem;
}
</style>
