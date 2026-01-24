<template>
	<header class="k-drawer-header">
		<k-breadcrumb
			:crumbs="crumbs"
			icon="bars"
			class="k-drawer-breadcrumb"
			@crumb="$emit('crumb', $event)"
		/>

		<k-drawer-tabs :tab="tab" :tabs="tabs" @open="$emit('tab', $event)" />

		<nav class="k-drawer-options">
			<slot />
			<k-button class="k-drawer-option" icon="check" type="submit" />
		</nav>
	</header>
</template>

<script>
export const props = {
	props: {
		/**
		 * An array of breadcrumb items
		 */
		breadcrumb: {
			default: () => [],
			type: Array
		},
		/**
		 * The name of the currently active tab
		 */
		tab: {
			type: String
		},
		/**
		 * An object with tab definitions.
		 */
		tabs: {
			default: () => ({}),
			type: Object
		}
	},
	computed: {
		crumbs() {
			return this.breadcrumb.map((crumb, index) => ({
				click: () => this.$emit("crumb", crumb.id),
				current: index === this.breadcrumb.length - 1,
				icon: crumb.props.icon,
				text: crumb.props.title,
				variant: "dimmed"
			}));
		}
	}
};

/**
 * @displayName DrawerHeader
 * @since 4.0.0
 */
export default {
	mixins: [props],
	emits: ["crumb", "tab"]
};
</script>

<style>
.k-drawer-header {
	--button-height: calc(var(--drawer-header-height) - var(--spacing-1));
	flex-shrink: 0;
	height: var(--drawer-header-height);
	padding-inline-start: var(--drawer-header-padding);
	display: flex;
	align-items: center;
	line-height: 1;
	justify-content: space-between;
	background: light-dark(var(--color-white), var(--color-gray-850));
	font-size: var(--text-sm);
}

.k-drawer-breadcrumb {
	flex: 1 1 auto;
	min-width: 0;
}

.k-drawer-options {
	display: flex;
	align-items: center;
	padding-inline-end: 0.75rem;
}
.k-drawer-option {
	--button-width: var(--button-height);
}
.k-drawer-option[aria-disabled="true"] {
	opacity: var(--opacity-disabled);
}
</style>
