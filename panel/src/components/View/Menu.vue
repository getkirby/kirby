<template>
	<nav
		class="k-panel-menu"
		:aria-label="$t('menu')"
		:data-hover="$panel.menu.hover"
		@mouseenter="$panel.menu.hover = true"
		@mouseleave="$panel.menu.hover = false"
	>
		<div class="k-panel-menu-body">
			<!-- Search button -->
			<k-button
				v-if="hasSearch"
				:text="$t('search')"
				icon="search"
				class="k-panel-menu-search k-panel-menu-button"
				@click="$panel.search()"
			/>

			<!-- Menus -->
			<menu
				v-for="(menu, menuIndex) in menus"
				:key="menuIndex"
				:data-second-last="menuIndex === menus.length - 2"
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
			<menu v-if="$panel.license === false" class="k-registration">
				<k-button
					icon="key"
					variant="filled"
					class="k-registration-button k-panel-menu-button"
					@click="$dialog('registration')"
				>
					Activate
				</k-button>

				<div class="k-registration-bubble">
					<p>
						<strong>Ready to launch your site?</strong>
						<a href="https://getkirby.com/buy" target="_blank">Buy a license</a>
						and
						<button type="button" @click="$dialog('registration')">
							activate it now
						</button>
					</p>
					<k-button class="k-registration-toggle" icon="cancel-small" />
				</div>
			</menu>
		</div>

		<!-- Collapse/expand toggle -->
		<k-button
			:icon="$panel.menu.isOpen ? 'angle-left' : 'angle-right'"
			:title="$panel.menu.isOpen ? $t('collapse') : $t('expand')"
			size="xs"
			class="k-panel-menu-toggle"
			@click="$panel.menu.toggle()"
		/>
	</nav>
</template>

<script>
/**
 * @since 4.0.0
 * @internal
 */
export default {
	data() {
		return {
			over: false
		};
	},
	computed: {
		hasSearch() {
			return this.$helper.object.length(this.$panel.searches) > 0;
		},
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
	width: var(--menu-width);

	background-color: var(--menu-color-back);
	border-right: 1px solid var(--menu-color-border);
	box-shadow: var(--menu-shadow);
}

/* Overscroll container for menu items. */
/* Overscrolling is needed if the screen height is too low */
.k-panel-menu-body {
	display: flex;
	flex-direction: column;
	/* A clamp keeps the gap large when there's enough vertical space */
	gap: var(--spacing-4);
	padding: var(--menu-padding);
	overscroll-behavior: contain;
	overflow-x: hidden;
	overflow-y: auto;
	height: 100%;
}

.k-panel-menu-search {
	margin-bottom: var(--spacing-8);
}

/** The vertical flex rules are useful for the body and button groups **/
.k-panel-menu-buttons {
	display: flex;
	flex-direction: column;
	width: 100%;
}
/* Keep the remaining space between 2nd last and last button group */
.k-panel-menu-buttons[data-second-last="true"] {
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

/* The toggle button builds a full-height strip on the side of the menu */
.k-panel-menu-toggle {
	--button-align: flex-start;
	--button-height: 100%;
	--button-width: var(--menu-toggle-width);
	position: absolute;
	inset-block: 0;
	inset-inline-start: 100%;
	align-items: flex-start;
	border-radius: 0;
	overflow: visible;
	opacity: 0;
	transition: opacity 0.2s;
}

/* The toggle strip has no focus style. The icon takes over here */
.k-panel-menu-toggle:focus {
	outline: 0;
}

/* The toggle icon has all the visible styles */
.k-panel-menu-toggle .k-button-icon {
	display: grid;
	place-items: center;
	height: var(--menu-toggle-height);
	width: var(--menu-toggle-width);
	margin-top: var(--menu-padding);
	border-block: 1px solid var(--menu-color-border);
	border-inline-end: 1px solid var(--menu-color-border);
	background: var(--menu-color-back);
	border-start-end-radius: var(--button-rounded);
	border-end-end-radius: var(--button-rounded);
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

	/* The toggle is visible on hover or focus */
	.k-panel-menu-toggle:focus-visible,
	/* The hover state is controlled via JS to avoid flickering */
	.k-panel-menu[data-hover] .k-panel-menu-toggle {
		opacity: 1;
	}
	/* Create the outline on the icon on focus */
	.k-panel-menu-toggle:focus-visible .k-button-icon {
		outline: var(--outline);
		/* With a radius on all ends, the outline looks nicer */
		border-radius: var(--button-rounded);
	}

	.k-panel-menu-search[aria-disabled="true"] {
		opacity: 0;
	}
}

/* Registration Button */
.k-registration-button {
	--button-color-back: var(--color-pink-300);
	--button-color-text: var(--color-pink-800);
	border: 1px solid var(--color-pink-400);
}

/* Registration Message */
.k-registration-bubble {
	position: absolute;
	display: flex;
	bottom: var(--menu-padding);
	height: var(--height-md);
	width: max-content;
	left: 100%;
	margin-left: var(--menu-padding);
	color: var(--dropdown-color-text);
	background: var(--dropdown-color-bg);
	border-radius: var(--dropdown-rounded);
	box-shadow: var(--dropdown-shadow);
}
.k-registration-bubble::before {
	position: absolute;
	content: "";
	top: 50%;
	left: -4px;
	margin-top: -4px;
	border-top: 4px solid transparent;
	border-right: 4px solid var(--color-black);
	border-bottom: 4px solid transparent;
}
.k-registration-bubble p {
	padding-inline-start: var(--spacing-3);
	padding-inline-end: var(--spacing-2);
	padding-block: 0.425rem;
	line-height: 1.25;
}
.k-registration-bubble p strong {
	font-weight: var(--font-normal);
	margin-inline-end: var(--spacing-1);
}
.k-registration-bubble p :where(button, a) {
	color: var(--color-pink-400);
	text-decoration: underline;
	text-decoration-color: currentColor;
	text-underline-offset: 2px;
	border-radius: var(--rounded-sm);
	padding-inline: var(--spacing-1);
}

/* Hide Button */
.k-registration-toggle {
	--button-color-text: var(--color-gray-400);
	--button-rounded: 0;
	border-left: 1px solid var(--dropdown-color-hr);
}
.k-registration-toggle:is(:hover, :focus) {
	--button-color-text: var(--color-white);
}
.k-registration-toggle:focus {
	--button-rounded: var(--rounded);
}
</style>
