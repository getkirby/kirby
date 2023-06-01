<template>
	<k-panel class="k-panel-inside" tabindex="0">
		<k-panel-menu :entries="$panel.menu" :view="$panel.view" />

		<main class="k-panel-main">
			<k-registration v-if="!$panel.license" />

			<div class="k-panel-view">
				<k-topbar :breadcrumb="$panel.view.breadcrumb" :view="$panel.view">
					<slot name="topbar" />
				</k-topbar>

				<slot />
			</div>
		</main>
	</k-panel>
</template>

<script>
export default {
	inheritAttrs: false
};
</script>

<style>
.k-panel-inside {
	display: flex;
}

.k-panel-main {
	--main-padding-inline: clamp(var(--spacing-3), 5cqw, var(--spacing-24));
	flex-grow: 1;
}

.k-panel-view {
	min-height: 100vh;
	min-height: 100dvh;
	padding: var(--spacing-3) var(--main-padding-inline) var(--spacing-24);
	container: main / inline-size;
}
</style>
