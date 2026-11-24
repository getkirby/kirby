<template>
	<k-scrollable class="k-dialog-body">
		<slot />
	</k-scrollable>
</template>

<script>
/**
 * @displayName DialogBody
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 */
export default {};
</script>

<style>
.k-dialog-body {
	padding: var(--dialog-padding);
}
.k-dialog:has(.k-dialog-footer) .k-dialog-body {
	padding-bottom: 0;
}
</style>
