<template>
	<Teleport v-if="visible" to=".k-drawer-portal">
		<form
			:class="$attrs.class"
			class="k-drawer"
			method="dialog"
			@submit.prevent="$emit('submit')"
		>
			<k-dropzone :disabled="!hasDropzone" @drop="$emit('drop', $event)">
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
								<k-dropdown
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
			</k-dropzone>
		</form>
	</Teleport>
</template>

<script>
import { getCurrentInstance } from "vue";
import Drawer from "@/mixins/drawer.js";

export default {
	mixins: [Drawer],
	emits: ["cancel", "crumb", "drop", "submit", "tab"],
	computed: {
		hasDropzone() {
			const instance = getCurrentInstance();
			return instance?.vnode?.props?.onDrop !== undefined;
		}
	}
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

/* Dropzone */
.k-drawer > .k-dropzone {
	min-height: 100%;
}
.k-drawer > .k-dropzone::after {
	border-radius: 0;
	outline-offset: -2px;
}
</style>
