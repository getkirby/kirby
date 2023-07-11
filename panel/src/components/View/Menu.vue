<template>
	<nav class="k-panel-menu" :data-collapsed="!$panel.menu.isOpen">
		<!-- Collapse/expand toggle -->
		<k-button
			:icon="$panel.menu.isOpen ? 'angle-left' : 'angle-right'"
			size="xs"
			class="k-panel-menu-toggle"
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
	--menu-color-border: var(--color-gray-300);
	--menu-toggle-width: 1rem;
	--menu-width: 12rem;
}

.k-panel-menu {
	height: 100vh;
	height: 100dvh;
	flex-shrink: 0;
	overscroll-behavior: contain;
	z-index: var(--z-navigation);
	display: flex;
	flex-direction: column;
	padding: var(--spacing-3);
	background-color: var(--menu-color-back);
	border-right: 1px solid var(--menu-color-border);
}

.k-panel-menu-search {
	margin-bottom: var(--spacing-12);
}
.k-panel-menu-toggle {
	--button-height: var(--height-md);
	--button-color-icon: var(--color-text);
	display: none;
	position: absolute;
	z-index: var(--z-dialog);
	width: var(--menu-toggle-width);
	inset-block-start: var(--spacing-3);
	inset-inline-end: calc(-1 * var(--menu-toggle-width));
	border-block: 1px solid var(--menu-color-border);
	border-inline-end: 1px solid var(--menu-color-border);
	background-color: var(--menu-color-back);
	border-start-start-radius: 0;
	border-end-start-radius: 0;
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

	:where(html, body):has(.k-panel-menu[data-collapsed="true"]) {
		overflow: hidden;
	}

	.k-panel-menu-collapse {
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

	.k-panel-menu .k-panel-menu-toggle {
		display: flex;
		opacity: 0;
		transition: opacity 0.2s ease-in-out;
		transition-delay: 0.2s;
	}
	.k-panel-menu:hover .k-panel-menu-toggle {
		opacity: 1;
		transition: opacity 0.1s ease-in-out;
		transition-delay: 0s;
	}

	/** invisible space on the right of the sidebar that help with hover state */
	.k-panel-menu::after {
		content: "";
		position: absolute;
		inset-block: 0;
		width: var(--menu-toggle-width);
		inset-inline-end: calc(-1 * var(--menu-toggle-width));
	}
}
</style>
