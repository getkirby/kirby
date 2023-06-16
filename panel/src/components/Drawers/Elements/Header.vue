<template>
	<header class="k-drawer-header">
		<h2 v-if="$panel.drawer.breadcrumb.length <= 1" class="k-drawer-title">
			<k-icon :type="$panel.drawer.icon" /> {{ $panel.drawer.title }}
		</h2>
		<ul v-else class="k-drawer-breadcrumb">
			<li v-for="crumb in $panel.drawer.breadcrumb" :key="crumb.id">
				<k-button
					:icon="crumb.icon"
					:text="crumb.title"
					@click="$panel.drawer.goTo(crumb.id)"
				/>
			</li>
		</ul>
		<k-drawer-tabs
			:tab="$panel.drawer.tabId"
			:tabs="$panel.drawer.tabs"
			@open="$panel.drawer.openTab($event)"
		/>
		<nav class="k-drawer-options">
			<slot />
			<k-button class="k-drawer-option" icon="check" type="submit" />
		</nav>
	</header>
</template>

<style>
.k-drawer-header {
	flex-shrink: 0;
	height: var(--drawer-header-height);
	padding-inline-start: var(--drawer-header-padding);
	display: flex;
	align-items: center;
	line-height: 1;
	justify-content: space-between;
	background: var(--color-white);
	font-size: var(--text-sm);
}
.k-drawer-title {
	padding: 0 0.75rem;
}
.k-drawer-title,
.k-drawer-breadcrumb {
	display: flex;
	flex-grow: 1;
	align-items: center;
	min-width: 0;
	margin-inline-start: -0.75rem;
	font-size: var(--text-sm);
	font-weight: var(--font-normal);
}
.k-drawer-breadcrumb li:not(:last-child) .k-button::after {
	position: absolute;
	inset-inline-end: -0.75rem;
	width: 1.5rem;
	display: inline-flex;
	justify-content: center;
	align-items: center;
	content: "›";
	color: var(--color-gray-500);
	height: var(--drawer-header-height);
}
.k-drawer-title .k-icon,
.k-drawer-breadcrumb .k-icon {
	width: 1rem;
	color: var(--color-gray-500);
	margin-inline-end: 0.5rem;
}
.k-drawer-breadcrumb .k-button {
	display: inline-flex;
	align-items: center;
	height: var(--drawer-header-height);
	padding-inline: 0.75rem;
}
.k-drawer-breadcrumb .k-button-text {
	opacity: 1;
}
.k-drawer-breadcrumb .k-button .k-button-icon ~ .k-button-text {
	padding-inline-start: 0;
}

.k-drawer-options {
	display: flex;
	align-items: center;
	padding-inline-end: 0.75rem;
}
.k-drawer-option {
	--button-height: calc(var(--drawer-header-height) - var(--spacing-1));
	--button-width: var(--button-height);
}
.k-drawer-option[aria-disabled] {
	opacity: var(--opacity-disabled);
}
</style>
