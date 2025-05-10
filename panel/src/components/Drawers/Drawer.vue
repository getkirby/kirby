<template>
	<portal v-if="visible" to="drawer">
		<form
			:class="$vnode.data.staticClass"
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
				<slot name="options">
					<template v-for="(option, index) in options">
						<template v-if="option.dropdown">
							<k-button
								:key="'btn-' + index"
								v-bind="option"
								class="k-drawer-option"
								@click="$refs['dropdown-' + index][0].toggle()"
							/>
							<k-dropdown-content
								:ref="'dropdown-' + index"
								:key="'dropdown-' + index"
								:options="option.dropdown"
								align-x="end"
								theme="light"
							/>
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
	--drawer-body-padding: 1.5rem;
	--drawer-color-back: var(--panel-color-back);
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
	--header-sticky-offset: calc(var(--drawer-body-padding) * -1);

	z-index: var(--z-toolbar);
	display: flex;
	flex-basis: var(--drawer-width);
	position: relative;
	display: flex;
	flex-direction: column;
	background: var(--drawer-color-back);
	box-shadow: var(--drawer-shadow);
	container-type: inline-size;
}
</style>
