<template>
	<footer class="k-dialog-footer">
		<slot />
	</footer>
</template>

<script>
/**
 * @displayName DialogFooter
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 */
export default {};
</script>

<style>
.k-dialog-footer {
	padding: var(--dialog-padding);
	line-height: 1;
	flex-shrink: 0;
}
</style>
