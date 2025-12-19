<template>
	<k-drawer
		ref="drawer"
		class="k-file-drawer"
		v-bind="{ breadcrumb, options, visible }"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', value)"
		@tab="$emit('tab', $event)"
	>
		<template #header>
			<k-form-controls
				:editor="editor"
				:has-diff="hasDiff"
				:is-locked="isLocked"
				:is-processing="isSaving"
				:modified="modified"
				size="xs"
				@discard="onDiscard"
				@submit="onSubmit"
			/>
		</template>

		<k-file-preview
			v-bind="preview"
			:content="content"
			:is-locked="isLocked"
			@input="onInput"
			@submit="onSubmit"
		/>

		<k-model-tabs :diff="diff" :tab="tab.name" :tabs="tabs" />

		<k-sections
			:blueprint="blueprint"
			:content="content"
			:empty="$t('file.blueprint', { blueprint: $esc(blueprint) })"
			:lock="lock"
			:parent="api"
			:tab="tab"
			@input="onInput"
			@submit="onSubmit"
		/>
	</k-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";
import FileView from "@/components/Views/Files/FileView.vue";

export default {
	extends: FileView,
	mixins: [Drawer],
	feature: "drawer",
	props: {
		tab: {
			type: Object,
			default() {
				return {
					columns: []
				};
			}
		}
	},
	emits: ["cancel", "input", "submit", "tab"]
};
</script>

<style>
.k-file-drawer .k-drawer-header {
	position: sticky;
	top: 0;
	z-index: var(--z-toolbar);
}

.k-file-drawer .k-form-controls-button {
	font-size: var(--text-xs);
	--button-rounded: 3px;
	--icon-size: 1rem;
}
</style>
