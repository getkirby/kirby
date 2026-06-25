<template>
	<div class="k-dialog-body">
		<slot />
	</div>
</template>

<script>
/**
 * @displayName DialogBody
 * @since 4.0.0
 */
export default {};
</script>

<style>
.k-dialog-body {
	min-height: 0;
	padding: var(--dialog-padding);
	overflow-y: auto;
	overscroll-behavior: contain;
}
.k-dialog:has(.k-dialog-footer) .k-dialog-body {
	padding-bottom: 0;
}
</style>
