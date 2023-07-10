<template>
	<nav class="k-panel-menu" :data-collapsed="!$panel.menu.isOpen">
		<!-- Collapse/expand handle -->
		<k-button
			icon="collapse"
			class="k-panel-menu-handle"
			@click="$panel.menu.toggle()"
		/>

		<!-- Search button -->
		<k-button
			:text="$t('search')"
			icon="search"
			class="k-panel-menu-search"
			@click="$panel.search()"
		/>

		<!-- Menus -->
		<menu
			v-for="(menu, menuIdex) in menus"
			:key="menuIdex"
			:data-is-second-last="menuIdex === menus.length - 2"
		>
			<k-button
				v-for="entry in menu"
				:key="entry.id"
				v-bind="entry"
				:click="() => (entry.dialog ? $dialog(entry.dialog) : null)"
				:current="entry.id === $panel.view.id"
				:title="entry.title ?? entry.text"
			/>
		</menu>
	</nav>
</template>

<script>
export default {
	computed: {
		menus() {
			return this.$panel.menu.entries.split("-");
		}
	}
};
</script>

<style>
:root {
	--menu-color-back: var(--color-gray-250);
	--menu-width: 12rem;
}

.k-panel-menu {
	height: 100vh;
	height: 100dvh;
	flex-shrink: 0;
	overflow-x: hidden;
	overflow-y: auto;
	overscroll-behavior: contain;
	z-index: var(--z-navigation);
	display: flex;
	flex-direction: column;
	padding: var(--spacing-3);
	background-color: var(--menu-color-back);
	border-right: 1px solid var(--color-gray-300);
}

.k-panel-menu-search {
	margin-bottom: var(--spacing-12);
}
.k-panel-menu menu + menu {
	margin-top: var(--spacing-6);
}
/** TODO: .k-panel-menu menu:has(+ :last-child)  */
.k-panel-menu menu[data-is-second-last="true"] {
	flex-grow: 1;
}

.k-panel-menu .k-button {
	--button-width: 100%;
	--button-text-display: var(--menu-buttons);
}
.k-panel-menu .k-button[aria-current] {
	--button-color-back: var(--color-white);
	box-shadow: var(--shadow);
}

@media (max-width: 40rem) {
	.k-panel-menu {
		position: absolute;
		inset-block: 0;
		inset-inline-start: 0;
		width: var(--menu-width);
		box-shadow: var(--shadow-xl);
	}

	.k-panel-menu[data-collapsed="true"] {
		display: none;
	}

	/* TODO: currently solved in panel.menu via JS
	:where(html, body):has(.k-panel-menu[data-collapsed="true"]) {
		overflow: hidden;
	} */

	.k-panel-menu-handle {
		display: none;
	}

	.k-panel-menu-search {
		margin-bottom: var(--spacing-6);
	}
	.k-panel-menu menu + menu {
		margin-top: var(--spacing-3);
	}
	.k-panel-menu .k-button {
		justify-content: flex-start;
	}
}

@media (min-width: 40rem) {
	.k-panel-menu {
		--menu-buttons: none;

		position: sticky;
		top: 0;
		width: calc(2.25rem + 2 * var(--spacing-3));
	}

	.k-panel-menu-handle,
	.k-panel-menu-handle .k-icon,
	.k-panel-menu-proxy {
		display: none;
	}
}

@media (min-width: 60rem) {
	.k-panel-menu:not([data-collapsed="true"]) {
		--menu-buttons: block;

		width: var(--menu-width);
	}

	.k-panel-menu:not([data-collapsed="true"]) .k-button {
		padding-inline-start: calc(var(--button-padding) + 0.125rem);
		justify-content: flex-start;
	}

	.k-panel-menu-handle {
		position: absolute;
		inset-block-start: calc(50% - 1.5rem);
		inset-inline-end: 0;
		width: 12px;
		height: 3rem;
		z-index: var(--z-dialog);
	}
	.k-panel-menu-handle::before {
		content: "";
		position: absolute;
		top: 0.5rem;
		inset-inline-end: 4px;
		width: 4px;
		height: 2rem;
		background-color: var(--color-gray-400);
		border-radius: var(--rounded);
	}
	.k-panel-menu-handle * {
		cursor: pointer;
	}
	.k-panel-menu:hover .k-panel-menu-handle {
		display: block;
	}
}
</style>
