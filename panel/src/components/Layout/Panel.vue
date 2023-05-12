<template>
	<div
		:data-dragging="$panel.drag.isDragging"
		:data-loading="$panel.isLoading"
		:data-language="$panel.language.code"
		:data-language-default="$panel.language.isDefault"
		:data-role="$panel.user.role"
		:data-translation="$panel.translation.code"
		:data-user="$panel.user.id"
		:dir="$panel.direction"
		class="k-panel"
	>
		<slot />

		<!-- Fiber dialogs -->
		<k-fiber-dialog v-if="$panel.dialog.isOpen && $panel.dialog.island" />

		<!-- Fiber drawers -->
		<k-fiber-drawer v-if="$panel.drawer.isOpen && $panel.drawer.island" />

		<!-- Fatal iframe -->
		<k-fatal
			v-if="$panel.notification.isFatal && $panel.notification.isOpen"
			:html="$panel.notification.message"
		/>

		<!-- Offline warning -->
		<k-offline-warning v-if="$panel.system.isLocal === false" />

		<!-- Icons -->
		<k-icons />

		<portal-target class="k-drawer-portal k-portal" name="drawer" multiple />
		<portal-target class="k-dialog-portal k-portal" name="dialog" multiple />
		<portal-target class="k-overlay-portal k-portal" name="overlay" multiple />
	</div>
</template>

<script>
export default {
	computed: {
		dialog() {
			return this.$helper.clone(this.$store.state.dialog);
		}
	}
};
</script>

<style>
.k-panel[data-loading="true"] {
	animation: LoadingCursor 0.5s;
}
.k-panel[data-loading="true"]::after,
.k-panel[data-dragging="true"] {
	user-select: none;
}
</style>
