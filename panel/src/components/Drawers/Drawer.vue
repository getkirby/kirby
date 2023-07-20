<template>
	<portal v-if="visible" to="drawer">
		<form
			:aria-disabled="disabled"
			:class="$vnode.data.staticClass"
			:data-expanded="$panel.drawer.isExpanded"
			class="k-drawer"
			method="dialog"
			@submit.prevent="$emit('submit')"
		>
			<k-drawer-notification />
			<k-drawer-header
				:breadcrumb="breadcrumb"
				:tab="tab"
				:tabs="tabs"
				@crumb="$emit('crumb', $event)"
				@tab="$emit('tab', $event)"
			>
				<k-expand-handle
					v-if="expandable"
					:is-expanded="$panel.drawer.isExpanded"
					attach="end"
					@update="$panel.drawer.isExpanded = $event"
				/>
				<slot name="options">
					<template v-for="(option, index) in options">
						<template v-if="option.dropdown">
							<k-dropdown :key="index">
								<k-button
									v-bind="option"
									class="k-drawer-option"
									@click="$refs['dropdown-' + index][0].toggle()"
								/>
								<k-dropdown-content
									:ref="'dropdown-' + index"
									:options="option.dropdown"
									align-x="end"
									theme="light"
								/>
							</k-dropdown>
						</template>

						<k-button
							v-else
							:key="index"
							v-bind="option"
							class="k-drawer-option"
						/>
					</template>
				</slot>
			</k-drawer-header>
			<k-drawer-body>
				<slot />
			</k-drawer-body>
		</form>
	</portal>
</template>

<script>
import Drawer from "@/mixins/drawer.js";

export default {
	mixins: [Drawer],
	emits: ["cancel", "crumb", "submit", "tab"]
};
</script>

<style>
:root {
	--drawer-color-back: var(--color-light);
	--drawer-header-height: 2.5rem;
	--drawer-header-padding: 1rem;
	--drawer-shadow: var(--shadow-xl);
	--drawer-width: 50rem;
}

/**
 * Don't apply the dark background twice
 * for nested drawers.
 */
.k-drawer-overlay + .k-drawer-overlay {
	--overlay-color-back: none;
}

.k-drawer {
	z-index: var(--z-toolbar);
	display: flex;
	flex-basis: var(--drawer-width);
	position: relative;
	flex-direction: column;
	background: var(--drawer-color-back);
	box-shadow: var(--drawer-shadow);
	container-type: inline-size;
}

@media (max-width: 60rem) {
	.k-drawer .k-expand-handle {
		display: none;
	}
}
@media (min-width: 60rem) {
	.k-drawer[data-expanded="true"] {
		flex-basis: min(calc(100% - var(--spacing-12)), 100rem);
	}
	.k-drawer:hover .k-expand-handle {
		opacity: 1;
	}
}
</style>
