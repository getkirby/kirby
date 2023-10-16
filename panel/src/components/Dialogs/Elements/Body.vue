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
	padding: var(--dialog-padding);
}
/** TODO: .k-dialog:has(.k-dialog-footer) .k-dialog-body */
.k-dialog[data-has-footer="true"] .k-dialog-body {
	padding-bottom: 0;
}
</style>
