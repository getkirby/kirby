<template>
	<header class="k-drawer-header">
		<nav class="k-breadcrumb k-drawer-breadcrumb">
			<ol>
				<li v-for="(crumb, index) in breadcrumb" :key="crumb.id">
					<k-button
						:icon="crumb.props.icon"
						:text="crumb.props.title"
						:current="index === breadcrumb.length - 1"
						variant="dimmed"
						class="k-breadcrumb-link"
						@click="$emit('crumb', crumb.id)"
					/>
				</li>
			</ol>
		</nav>
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
	flex-grow: 1;
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
