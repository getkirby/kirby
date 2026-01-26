<template>
	<k-panel-inside
		:data-id="id"
		:data-locked="isLocked"
		:data-template="blueprint"
		class="k-file-view"
	>
		<template #topbar>
			<k-prev-next :prev="prev" :next="next" />
		</template>

		<k-header
			:editable="permissions.changeName && !isLocked"
			class="k-file-view-header"
			@edit="$dialog(api + '/changeName')"
		>
			{{ filename }}

			<template #buttons>
				<k-view-buttons :buttons="buttons">
					<template #after>
						<k-form-controls
							:can-save="permissions.save"
							:editor="editor"
							:has-diff="hasDiff"
							:is-locked="isLocked"
							:is-processing="isSaving"
							:modified="modified"
							@discard="onDiscard"
							@submit="onSubmit"
						/>
					</template>
				</k-view-buttons>
			</template>
		</k-header>

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
	</k-panel-inside>
</template>

<script>
import ModelView from "../ModelView.vue";

export default {
	extends: ModelView,
	props: {
		extension: String,
		filename: String,
		mime: String,
		preview: Object,
		type: String,
		url: String
	},
	methods: {
		onAction(action) {
			switch (action) {
				case "replace":
					return this.$panel.upload.replace({
						extension: this.extension,
						filename: this.filename,
						image: this.preview.image,
						link: this.link,
						mime: this.mime,
						url: this.url
					});
			}
		}
	}
};
</script>

<style>
.k-file-view-header {
	margin-bottom: 0;
	border-bottom: 0;
}
</style>
