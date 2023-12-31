<template>
	<div
		:data-dragging="$panel.drag.isDragging"
		:data-loading="$panel.isLoading"
		:data-language="$panel.language.code"
		:data-language-default="$panel.language.isDefault"
		:data-menu="$panel.menu.isOpen ? 'true' : 'false'"
		:data-role="$panel.user.role"
		:data-translation="$panel.translation.code"
		:data-user="$panel.user.id"
		:dir="$panel.direction"
		class="k-panel"
	>
		<slot />

		<!-- Fiber dialogs -->
		<k-fiber-dialog v-if="$panel.dialog.isOpen && !$panel.dialog.legacy" />

		<!-- Fiber drawers -->
		<k-fiber-drawer v-if="$panel.drawer.isOpen && !$panel.drawer.legacy" />

		<!-- Fatal iframe -->
		<k-fatal
			v-if="$panel.notification.isFatal && $panel.notification.isOpen"
			:html="$panel.notification.message"
		/>

		<!-- Offline warning -->
		<k-offline-warning />

		<!-- Icons -->
		<k-icons />

		<k-overlay
			:nested="$panel.drawer.history.milestones.length > 1"
			:visible="$panel.drawer.isOpen"
			type="drawer"
			@close="$panel.drawer.close()"
		>
			<div class="k-drawer-portal k-portal" multiple />
		</k-overlay>

		<k-overlay
			:visible="$panel.dialog.isOpen"
			type="dialog"
			@close="$panel.dialog.close()"
		>
			<div class="k-dialog-portal k-portal" multiple />
		</k-overlay>
	</div>
</template>

<script>
/**
 * @internal
 */
export default {};
</script>

<style>
:root {
	--scroll-top: 0rem;
}

html {
	overflow-x: hidden;
	overflow-y: scroll;
	background: var(--color-light);
}

body {
	font-size: var(--text-sm);
}

.k-panel[data-loading="true"] {
	animation: LoadingCursor 0.5s;
}
@keyframes LoadingCursor {
	100% {
		cursor: progress;
	}
}

.k-panel[data-loading="true"]::after,
.k-panel[data-dragging="true"] {
	user-select: none;
}
</style>
