<template>
	<k-panel-inside
		:data-has-tabs="hasTabs"
		:data-id="id"
		:data-locked="isLocked"
		:data-template="blueprint"
		class="k-page-view"
	>
		<template #topbar>
			<k-prev-next :prev="prev" :next="next" />
		</template>

		<k-header
			:editable="permissions.changeTitle && !isLocked"
			class="k-page-view-header"
			@edit="$dialog(api + '/changeTitle')"
		>
			{{ title }}

			<template #buttons>
				<k-view-buttons :buttons="buttons" />
				<k-form-controls
					:editor="editor"
					:is-locked="isLocked"
					:is-unsaved="isUnsaved"
					:modified="modified"
					:preview="permissions.preview ? api + '/preview/compare' : false"
					@discard="onDiscard"
					@submit="onSubmit"
				/>
			</template>
		</k-header>

		<k-model-tabs :tab="tab.name" :tabs="tabs" />

		<k-sections
			:blueprint="blueprint"
			:content="content"
			:empty="$t('page.blueprint', { blueprint: $esc(blueprint) })"
			:lock="lock"
			:parent="api"
			:tab="tab"
			@input="onInput"
			@submit="onSubmit"
		/>
	</k-panel-inside>
</template>

<script>
import ModelView from "../ModelView.vue";

export default {
	extends: ModelView,
	props: {
		title: String
	},
	computed: {
		protectedFields() {
			return ["title"];
		}
	}
};
</script>

<style>
/** TODO: .k-page-view:has(.k-tabs) .k-page-view-header */
.k-page-view[data-has-tabs="true"] .k-page-view-header {
	margin-bottom: 0;
}
</style>
