<template>
	<k-inside
		:data-locked="isLocked"
		data-id="/"
		data-template="site"
		class="k-site-view"
	>
		<k-header
			:editable="permissions.changeTitle && !isLocked"
			@edit="$dialog('site/changeTitle')"
		>
			{{ model.title }}
			<template #buttons>
				<k-button-group>
					<k-button
						:link="model.previewUrl"
						:responsive="true"
						:text="$t('open')"
						icon="open"
						target="_blank"
						variant="filled"
						size="sm"
						class="k-site-view-preview"
					/>
					<k-languages-dropdown />
				</k-button-group>

				<k-form-buttons :lock="lock" />
			</template>
		</k-header>

		<k-model-tabs :tab="tab.name" :tabs="tabs" />

		<k-sections
			:blueprint="blueprint"
			:empty="$t('site.blueprint')"
			:lock="lock"
			:tab="tab"
			parent="site"
			@submit="$emit('submit', $event)"
		/>
	</k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
	extends: ModelView,
	computed: {
		protectedFields() {
			return ["title"];
		}
	}
};
</script>
