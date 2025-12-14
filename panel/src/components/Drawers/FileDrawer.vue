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
			<k-form-controls :has-diff="true" size="xs" />
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
