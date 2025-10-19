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
			<span
				v-if="!title || title.length === 0"
				class="k-site-title-placeholder"
			>
				{{ $t("view.site") }} …
			</span>
			<template v-else>
				{{ title }}
			</template>

			<template #buttons>
				<k-view-buttons :buttons="buttons" />
				<k-form-controls
					:editor="editor"
					:has-diff="hasDiff"
					:is-locked="isLocked"
					:is-processing="isSaving"
					:modified="modified"
					:preview="permissions.preview ? api + '/preview/changes' : false"
					@discard="onDiscard"
					@submit="onSubmit"
				/>
			</template>
		</k-header>

		<k-model-tabs :diff="diff" :tab="tab.name" :tabs="tabs" />

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
.k-site-title-placeholder {
	color: var(--color-gray-500);
	transition: color 0.3s;
}
.k-site-view-header[data-editable="true"] .k-site-title-placeholder:hover {
	color: var(--color-gray-900);
}
.k-site-view[data-has-tabs="true"] .k-site-view-header {
	margin-bottom: 0;
}
</style>
