<template>
	<nav class="k-panel-menu">
		<!-- Collapse/expand handle -->
		<label class="k-panel-menu-handle">
			<k-icon type="collapse" />
			<input
				ref="handle"
				type="checkbox"
				name="menu"
				data-variant="invisible"
				@input="onHandle"
			/>
		</label>

		<!-- Search button -->
		<k-button
			:text="$t('search')"
			icon="search"
			class="k-panel-menu-search"
			@click="$refs.search.open()"
		/>

		<!-- Menus -->
		<menu v-for="(menu, menuIdex) in menus" :key="menuIdex">
			<k-button
				v-for="entry in menu"
				:key="entry.id"
				v-bind="entry"
				:current="entry.id === view.id"
				:title="entry.title ?? entry.text"
				:variant="entry.id === view.id ? 'filled' : null"
			/>
		</menu>

		<!-- Search dialog -->
		<k-search ref="search" :type="view.search || 'pages'" :types="$searches" />
	</nav>
</template>

<script>
export default {
	props: {
		entries: Array,
		view: Object
	},
	data() {
		return {
			media: null
		};
	},
	computed: {
		menus() {
			return this.entries.split("-");
		}
	},
	mounted() {
		this.media = window.matchMedia("(max-width: 40rem)");
		this.onCSSMediaChange(this.media);
		this.media.addEventListener("change", this.onCSSMediaChange);
		this.$events.$on("keydown.esc", this.onEscape);
	},
	destroyed() {
		this.media.removeEventListener("change", this.onCSSMediaChange);
		this.$events.$off("keydown.esc", this.onEscape);
	},
	methods: {
		close() {
			if (this.$refs.handle) {
				this.$refs.handle.checked = false;
				this.onHandle();
			}
		},
		onClick(event) {
			if (
				document.querySelector(".k-panel-menu-proxy").contains(event.target) ===
					false &&
				this.$el.contains(event.target) === false
			) {
				this.close();
			}
		},
		onCSSMediaChange(media) {
			// when resizing to mobile, make sure menu starts closed
			if (media.matches === true) {
				this.close();
			} else if (localStorage.getItem("kirby$menu") !== null) {
				// only restore collapse/expanded state when not mobile
				this.$refs.handle.checked = true;
			}
		},
		onEscape() {
			return this.media.matches ? this.close() : null;
		},
		onHandle() {
			if (this.$refs.handle.checked) {
				if (this.media.matches) {
					document.addEventListener("click", this.onClick);
				} else {
					localStorage.setItem("kirby$menu", true);
				}
			} else {
				if (this.media.matches) {
					document.removeEventListener("click", this.onClick);
				} else {
					localStorage.removeItem("kirby$menu");
				}
			}
		}
	}
};
</script>

<style>
:root {
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
	background-color: var(--color-slate-300);
}

.k-panel-menu-search {
	margin-bottom: var(--spacing-12);
}
.k-panel-menu menu + menu {
	margin-top: var(--spacing-6);
}
.k-panel-menu menu:has(+ :last-child) {
	flex-grow: 1;
}

.k-panel-menu .k-button {
	--button-width: 100%;
	--button-text-display: var(--menu-buttons);
}

@media (max-width: 40rem) {
	.k-panel-menu {
		position: absolute;
		inset-block: 0;
		inset-inline-start: 0;
		width: var(--menu-width);
		box-shadow: var(--shadow-xl);
	}

	.k-panel-menu:not(:has([name="menu"]:checked)) {
		display: none;
	}

	:where(html, body):has([name="menu"]:checked) {
		overflow: hidden;
	}

	.k-panel-menu-handle {
		height: var(--button-height);
		padding-inline: var(--button-padding);
		margin-bottom: var(--spacing-1);
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
	.k-panel-menu:has([name="menu"]:checked) {
		--menu-buttons: block;

		width: var(--menu-width);
	}

	.k-panel-menu:has([name="menu"]:checked) .k-button {
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
		background-color: var(--color-slate-400);
		border-radius: var(--rounded);
	}
	.k-panel-menu-handle * {
		cursor: pointer;
	}
	.k-panel-menu:hover .k-panel-menu-handle {
		display: block;
	}
}

/** @todo Temporary fixes */
input:where([type="checkbox"], [type="radio"])[data-variant="invisible"] {
	position: absolute;
	size: 0;
	border: 0;
	opacity: 0;
}
</style>
