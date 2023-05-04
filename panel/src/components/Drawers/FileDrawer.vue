<template>
	<k-drawer
		ref="drawer"
		v-bind="$props"
		class="k-file-drawer"
		@cancel="cancel"
		@submit="submit"
	>
		<slot name="options" slot="options" />
		<k-file-preview v-bind="preview" />
		<k-drawer-fields
			:fields="$panel.drawer.tab?.fields"
			:value="value"
			@input="input"
			@submit="submit"
		/>
	</k-drawer>
</template>

<script>
import FormDrawer from "./FormDrawer.vue";

export default {
	mixins: [FormDrawer],
	props: {
		preview: {
			type: Object
		}
	}
};
</script>

<style>
.k-file-drawer .k-drawer-body {
	padding: 0;
}
.k-file-drawer .k-drawer-fields {
	padding: var(--spacing-6);
}
.k-file-drawer .k-file-preview .k-view {
	padding: 0;
}
.k-file-drawer .k-file-preview-details dt {
	margin-bottom: 0;
}
.k-file-drawer .k-file-preview-layout {
	grid-template-columns: 33% auto;
}
.k-file-drawer .k-drawer-header {
	background: var(--color-gray-900);
	color: var(--color-white);
}
.k-file-drawer .k-file-preview .k-dropdown {
	display: none;
}
</style>
