<template>
	<k-panel-inside
		:data-has-tabs="hasTabs"
		:data-id="id"
		:data-locked="isLocked"
		:data-template="blueprint"
		class="k-site-view"
	>
		<k-header
			:editable="permissions.changeTitle && !isLocked"
			class="k-site-view-header"
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
					@discard="onDiscard"
					@submit="onSubmit"
				/>
			</template>
		</k-header>

		<k-model-tabs :tab="tab.name" :tabs="tabs" />

		<k-sections
			:blueprint="blueprint"
			:content="content"
			:empty="$t('site.blueprint')"
			:lock="lock"
			:tab="tab"
			parent="site"
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
	}
};
</script>

<style>
/** TODO: .k-site-view:has(.k-tabs) .k-site-view-header */
.k-site-view[data-has-tabs="true"] .k-site-view-header {
	margin-bottom: 0;
}
</style>
