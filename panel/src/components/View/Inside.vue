<template>
	<k-panel class="k-panel-inside">
		<k-panel-menu
			v-bind="$panel.menu.props"
			:hover="$panel.menu.hover"
			:is-open="$panel.menu.isOpen"
			:license="$panel.license"
			:searches="$panel.searches"
			@hover="$panel.menu.hover = $event"
			@search="$panel.search()"
			@toggle="$panel.menu.toggle()"
		/>
		<main class="k-panel-main">
			<k-topbar :breadcrumb="$panel.view.breadcrumb" :view="$panel.view">
				<!-- @slot Additional content for the Topbar  -->
				<slot name="topbar" />
			</k-topbar>

			<!-- @slot Main content for the view  -->
			<slot />
		</main>

		<!-- Notifications -->
		<k-button
			v-if="notification && notification.type !== 'error'"
			:icon="notification.icon"
			:text="notification.message"
			:theme="notification.theme"
			variant="filled"
			class="k-panel-notification"
			@click="notification.close()"
		/>
	</k-panel>
</template>

<script>
/**
 * Wrapper for views that are available only for signed-in users.
 * @displayName PanelInside
 */
export default {
	computed: {
		notification() {
			if (
				this.$panel.notification.context === "view" &&
				!this.$panel.notification.isFatal
			) {
				return this.$panel.notification;
			}

			return null;
		}
	}
};
</script>

<style>
:root {
	--main-padding-inline: clamp(var(--spacing-6), 5cqw, var(--spacing-24));
}

.k-panel-main {
	min-height: 100vh;
	min-height: 100dvh;
	padding: var(--spacing-3) var(--main-padding-inline) var(--spacing-24);
	container: main / inline-size;
	margin-inline-start: var(--main-start);
}

.k-panel-notification {
	--button-height: var(--height-md);
	--button-color-icon: var(--theme-color-900);
	--button-color-text: var(--theme-color-900);
	border: 1px solid var(--theme-color-500);
	position: fixed;
	inset-block-end: var(--menu-padding);
	inset-inline-end: var(--menu-padding);
	box-shadow: var(--dropdown-shadow);
	z-index: var(--z-notification);
}
</style>
