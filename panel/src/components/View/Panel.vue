<template>
	<div
		:data-dragging="$panel.drag.isDragging"
		:data-loading="$panel.isLoading"
		:data-language="$panel.language.code"
		:data-language-default="$panel.language.isDefault"
		:data-menu="$panel.menu.isOpen ? 'true' : 'false'"
		:data-role="$panel.user.role"
		:data-theme="$panel.theme.current"
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
			<portal-target class="k-drawer-portal k-portal" name="drawer" multiple />
		</k-overlay>

		<k-overlay
			:visible="$panel.dialog.isOpen"
			type="dialog"
			@close="$panel.dialog.close()"
		>
			<portal-target class="k-dialog-portal k-portal" name="dialog" multiple />
		</k-overlay>

		<portal-target class="k-overlay-portal k-portal" name="overlay" multiple />
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
	--panel-color-back: light-dark(var(--color-gray-200), var(--color-gray-900));
	--scroll-top: 0rem;
}

html {
	overflow-x: hidden;
	overflow-y: scroll;
	background: var(--panel-color-back);
	color: var(--color-text);
}

body {
	font-size: var(--text-sm);
	color: var(--color-text);
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
