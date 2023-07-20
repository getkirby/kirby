<template>
	<nav
		class="k-panel-menu"
		:data-hover="$panel.menu.hover"
		@mouseenter="$panel.menu.hover = true"
		@mouseleave="$panel.menu.hover = false"
	>
		<div class="k-panel-menu-body">
			<!-- Search button -->
			<k-button
				:text="$t('search')"
				icon="search"
				class="k-panel-menu-search k-panel-menu-button"
				@click="$panel.search()"
			/>
			<!-- Menus -->
			<menu
				v-for="(menu, menuIdex) in menus"
				:key="menuIdex"
				class="k-panel-menu-buttons"
			>
				<k-button
					v-for="entry in menu"
					:key="entry.id"
					v-bind="entry"
					:title="entry.title ?? entry.text"
					class="k-panel-menu-button"
				/>
			</menu>
		</div>

		<!-- Collapse/expand toggle -->
		<k-expand-handle
			:is-expanded="$panel.menu.isOpen"
			class="k-panel-menu-expand"
			@update="$panel.menu.toggle()"
		/>
	</nav>
</template>

<script>
export default {
	data() {
		return {
			over: false
		};
	},
	computed: {
		menus() {
			return this.$panel.menu.entries.split("-");
		}
	}
};
</script>

<style>
:root {
	--menu-button-height: var(--height);
	--menu-button-width: 100%;
	--menu-color-back: var(--color-gray-250);
	--menu-color-border: var(--color-gray-300);
	--menu-display: none;
	--menu-display-backdrop: block;
	--menu-padding: var(--spacing-3);
	--menu-shadow: var(--shadow-xl);
	--menu-toggle-height: var(--menu-button-height);
	--menu-toggle-width: 1rem;
	--menu-width-closed: calc(
		var(--menu-button-height) + 2 * var(--menu-padding)
	);
	--menu-width-open: 12rem;
	--menu-width: var(--menu-width-open);
}

/* Main menu element controls positioning */
.k-panel-menu {
	position: fixed;
	inset-inline-start: 0;
	inset-block: 0;
	z-index: var(--z-navigation);
	display: var(--menu-display);
	background-color: var(--menu-color-back);
	border-right: 1px solid var(--menu-color-border);
	width: var(--menu-width);
	box-shadow: var(--menu-shadow);
}

/* The toggle button */
.k-panel-menu-expand {
	--expand-handle-back: var(--menu-color-back);
}
.k-panel-menu-expand .k-button-icon {
	height: var(--menu-toggle-height);
	width: var(--menu-toggle-width);
	margin-top: var(--menu-padding);
	border-block: 1px solid var(--menu-color-border);
	border-inline-end: 1px solid var(--menu-color-border);
}
/* The hover state is controlled via JS to avoid flickering */
.k-panel-menu[data-hover="true"] .k-panel-menu-expand {
	opacity: 1;
}

/* Overscroll container for menu items. Overscrolling is needed if the screen height is too low */
.k-panel-menu-body {
	/* A clamp keeps the gap large when there's enough vertical space */
	gap: clamp(var(--spacing-3), 9vh, var(--spacing-12));
	padding: var(--menu-padding);
	overscroll-behavior: contain;
	overflow-x: hidden;
	overflow-y: auto;
	height: 100%;
}

/** The vertical flex rules are useful for the body and button groups **/
.k-panel-menu-body,
.k-panel-menu-buttons {
	display: flex;
	flex-direction: column;
	width: 100%;
	flex-grow: 1;
}

/* Move the last menu to the end */
.k-panel-menu-buttons:last-child {
	justify-content: flex-end;
}

/* Menu buttons incl. search */
.k-panel-menu-button {
	--button-align: flex-start;
	--button-height: var(--menu-button-height);
	--button-width: var(--menu-button-width);
	/* Make sure that buttons don't shrink in height */
	flex-shrink: 0;
}

.k-panel-menu-button[aria-current] {
	--button-color-back: var(--color-white);
	box-shadow: var(--shadow);
}

/* Outline should not vanish behind other buttons */
.k-panel-menu-button:focus {
	z-index: 1;
}

/* The open menu state works for all screen sizes */
.k-panel[data-menu="true"] {
	--menu-button-width: 100%;
	--menu-display: block;
	--menu-width: var(--menu-width-open);
}

/* Backdrop for the mobile menu */
.k-panel[data-menu="true"]::after {
	content: "";
	position: fixed;
	inset: 0;
	background: var(--color-backdrop);
	display: var(--menu-display-backdrop);
	pointer-events: none;
}

/* Desktop size */
@media (min-width: 60rem) {
	/* The menu is always visible on desktop sizes */
	.k-panel {
		--menu-display: block;
		--menu-display-backdrop: none;
		--menu-shadow: none;

		/* The main view is indented according to the menu width */
		--main-start: var(--menu-width);
	}

	/* Closed state on desktop with square buttons */
	.k-panel[data-menu="false"] {
		--menu-button-width: var(--menu-button-height);
		--menu-width: var(--menu-width-closed);
	}

	/* No proxy button in the breadcrumb. The toggle is enough. */
	.k-panel-menu-proxy {
		display: none;
	}
}
</style>
