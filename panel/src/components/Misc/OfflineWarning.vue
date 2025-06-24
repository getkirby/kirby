<template>
	<div v-if="!$panel.system.isLocal && $panel.isOffline" class="k-offline-warning">
		<p><k-icon type="bolt" /> {{ $t("error.offline") }}</p>
	</div>
</template>

<script>
/**
 * @internal
 */
export default {};
</script>

<style>
.k-offline-warning {
	position: fixed;
	inset: 0;
	z-index: var(--z-offline);
	background: var(--overlay-color-back);
	display: flex;
	align-items: center;
	justify-content: center;
	line-height: 1;
}
.k-offline-warning p {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	background: var(--color-white);
	box-shadow: var(--shadow);
	padding: 0.75rem;
	border-radius: var(--rounded);
}
.k-offline-warning p .k-icon {
	color: var(--color-red-400);
}
</style>
