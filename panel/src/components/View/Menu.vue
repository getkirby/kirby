<template>
	<nav
		class="k-panel-menu"
		:aria-label="$t('menu')"
		:data-hover="isHovered"
		@mouseenter="$emit('hover', true)"
		@mouseleave="$emit('hover', false)"
	>
		<div class="k-panel-menu-body">
			<!-- Search button -->
			<k-button
				v-if="hasSearch"
				:text="$t('search')"
				icon="search"
				class="k-panel-menu-search k-panel-menu-button"
				@click="$emit('search')"
			/>

			<!-- Menus -->
			<menu
				v-for="(menu, menuIndex) in menus"
				:key="menuIndex"
				:data-second-last="menuIndex === menus.length - 2"
				class="k-panel-menu-buttons"
			>
				<template v-for="entry in menu">
					<component
						:is="entry.component"
						:key="entry.key"
						v-bind="entry.props"
						class="k-panel-menu-button"
					/>
				</template>
			</menu>

			<menu v-if="activationButton">
				<k-button
					v-bind="activationButton"
					class="k-activation-button k-panel-menu-button"
					icon="key"
					theme="love"
					variant="filled"
				/>
				<k-activation :status="license" />
			</menu>
		</div>

		<!-- Collapse/expand toggle -->
		<k-button
			:icon="isOpen ? 'angle-left' : 'angle-right'"
			:title="isOpen ? $t('collapse') : $t('expand')"
			size="xs"
			class="k-panel-menu-toggle"
			@click="$emit('toggle')"
		/>
	</nav>
</template>

<script>
/**
 * @since 4.0.0
 * @internal
 */
export default {
	props: {
		isHovered: Boolean,
		isOpen: Boolean,
		items: {
			type: Array,
			default: () => []
		},
		license: String,
		searches: {
			type: Object,
			default: () => ({})
		}
	},
	emits: ["search", "toggle"],
	computed: {
		activationButton() {
			if (this.license === "missing") {
				return {
					click: () => this.$dialog("registration"),
					text: this.$t("activate")
				};
			}

			if (this.license === "legacy") {
				return {
					click: () => this.$dialog("license"),
					text: this.$t("renew")
				};
			}

			return false;
		},
		hasSearch() {
			return this.$helper.object.length(this.searches) > 0;
		},
		menus() {
			return this.$helper.array.split(this.items, "-");
		}
	}
};
</script>

<style>
:root {
	--menu-button-height: var(--height);
	--menu-button-width: 100%;
	--menu-color-back: light-dark(var(--color-gray-250), var(--color-gray-950));
	--menu-color-border: light-dark(var(--color-gray-300), var(--color-gray-850));
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
	margin-bottom: auto;
}
/* Menu buttons incl. search */
.k-panel-menu-button {
	--button-align: flex-start;
	--button-height: var(--menu-button-height);
	--button-width: var(--menu-button-width);
	--button-padding: 7px;
	/* Make sure that buttons don't shrink in height */
	flex-shrink: 0;
}
.k-panel-menu-button[aria-current="true"] {
	--button-color-back: light-dark(var(--color-white), var(--color-gray-850));
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
	background: var(--overlay-color-back);
	display: var(--menu-display-backdrop);
	pointer-events: none;
	z-index: var(--z-drawer);
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

@media (max-width: 60rem) {
	.k-panel-menu .k-activation-button {
		margin-bottom: var(--spacing-3);
	}
	.k-panel-menu .k-activation-toggle {
		display: none;
	}
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
	.k-panel-menu[data-hover="true"] .k-panel-menu-toggle {
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

	.k-panel-menu .k-activation {
		position: absolute;
		bottom: var(--menu-padding);
		inset-inline-start: 100%;
		height: var(--height-md);
		width: max-content;
		margin-left: var(--menu-padding);
	}
	.k-panel-menu .k-activation::before {
		position: absolute;
		content: "";
		top: 50%;
		left: -4px;
		margin-top: -4px;
		border-top: 4px solid transparent;
		border-right: 4px solid var(--color-black);
		border-bottom: 4px solid transparent;
	}
	.k-panel-menu .k-activation p :where(button, a) {
		padding-inline: var(--spacing-1);
	}
	.k-panel-menu .k-activation-toggle {
		border-left: 1px solid var(--dropdown-color-hr);
	}
}
</style>
