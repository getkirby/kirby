<template>
	<div
		v-if="$panel.notification.isOpen"
		:data-theme="$panel.notification.theme"
		class="k-notification"
	>
		<p>{{ $panel.notification.message }}</p>
		<k-button icon="cancel" @click="$panel.notification.close()" />
	</div>
</template>

<script>
/**
 * Displays notification from `panel.notification`
 * @unstable
 * @since 4.0.0
 */
export default {};
</script>

<style>
.k-notification {
	padding: 0.75rem 1.5rem;
	background: var(--color-gray-900);
	width: 100%;
	line-height: 1.25rem;
	color: var(--color-white);
	display: flex;
	flex-shrink: 0;
	align-items: center;
}

.k-notification[data-theme] {
	background: var(--theme-color-back);
	color: var(--color-black);
}

.k-notification p {
	flex-grow: 1;
	word-wrap: break-word;
	overflow: hidden;
}

.k-notification .k-button {
	display: flex;
	margin-inline-start: 1rem;
}
</style>
