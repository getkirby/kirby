<template>
	<k-panel class="k-panel-inside">
		<k-panel-menu />
		<main class="k-panel-main">
			<k-topbar :breadcrumb="$panel.view.breadcrumb" :view="$panel.view">
				<slot name="topbar" />
			</k-topbar>

			<slot />
		</main>
	</k-panel>
</template>

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
