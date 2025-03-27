<template>
	<k-panel-inside
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
					:has-changes="hasChanges"
					:is-locked="isLocked"
					:modified="modified"
					:preview="permissions.preview ? api + '/preview/compare' : false"
					@discard="onDiscard"
					@submit="onSubmit"
				/>
			</template>
		</k-header>

		<k-model-tabs :changes="changes" :tab="tab.name" :tabs="tabs" />

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
.k-site-view[data-has-tabs="true"] .k-site-view-header {
	margin-bottom: 0;
}
</style>
