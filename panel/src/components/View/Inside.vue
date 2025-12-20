<template>
	<k-panel class="k-panel-inside">
		<k-panel-menu
			:has-search="$panel.hasSearch"
			:items="$panel.menu.items"
			:is-hovered="$panel.menu.hover"
			:is-open="$panel.menu.isOpen"
			:license="$panel.license"
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

		<k-panel-notifications />
	</k-panel>
</template>

<script>
/**
 * Wrapper for views that are available only for signed-in users.
 * @displayName PanelInside
 */
export default {};
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
</style>
