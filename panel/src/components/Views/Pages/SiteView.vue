<template>
	<k-panel-inside
		:data-has-tabs="tabs.length > 1"
		:data-locked="isLocked"
		data-id="/"
		data-template="site"
		class="k-site-view"
	>
		<k-header
			:editable="permissions.changeTitle && !isLocked"
			class="k-site-view-header"
			@edit="$dialog('site/changeTitle')"
		>
			{{ model.title }}

			<template #buttons>
				<k-view-buttons :buttons="buttons" />
				<k-form-controls
					:changes="changes"
					:lock="lock"
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
	emits: ["submit"],
	computed: {
		protectedFields() {
			return ["title"];
		}
	}
};
</script>

<style>
/** TODO: .k-site-view:has(.k-tabs) .k-site-view-header */
.k-site-view[data-has-tabs="true"] .k-site-view-header {
	margin-bottom: 0;
}
</style>
