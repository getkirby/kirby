<template>
	<k-panel-inside
		:data-has-tabs="tabs.length > 1"
		:data-id="model.id"
		:data-locked="isLocked"
		:data-template="blueprint"
		class="k-page-view"
	>
		<template #topbar>
			<k-prev-next v-if="model.id" :prev="prev" :next="next" />
		</template>

		<k-header
			:editable="permissions.changeTitle && !isLocked"
			class="k-page-view-header"
			@edit="$dialog(id + '/changeTitle')"
		>
			{{ model.title }}
			<template #buttons>
				<k-button-group>
					<k-button
						v-if="permissions.preview && model.previewUrl"
						:link="model.previewUrl"
						:title="$t('open')"
						icon="open"
						target="_blank"
						variant="filled"
						size="sm"
						class="k-page-view-preview"
					/>

					<k-button
						:disabled="isLocked === true"
						:dropdown="true"
						:title="$t('settings')"
						icon="cog"
						variant="filled"
						size="sm"
						class="k-page-view-options"
						@click="$refs.settings.toggle()"
					/>
					<k-dropdown-content
						ref="settings"
						:options="$dropdown(id)"
						align-x="end"
					/>

					<k-languages-dropdown />

					<k-button
						v-if="status"
						v-bind="statusBtn"
						class="k-page-view-status"
						@click="$dialog(id + '/changeStatus')"
					/>
				</k-button-group>

				<k-form-buttons :lock="lock" />
			</template>
		</k-header>

		<k-model-tabs :tab="tab.name" :tabs="tabs" />

		<k-sections
			:blueprint="blueprint"
			:empty="$t('page.blueprint', { blueprint: $esc(blueprint) })"
			:lock="lock"
			:parent="id"
			:tab="tab"
		/>
	</k-panel-inside>
</template>

<script>
import ModelView from "../ModelView.vue";

export default {
	extends: ModelView,
	props: {
		status: Object
	},
	computed: {
		protectedFields() {
			return ["title"];
		},
		statusBtn() {
			return {
				...this.$helper.page.status.call(
					this,
					this.model.status,
					!this.permissions.changeStatus || this.isLocked
				),
				responsive: true,
				size: "sm",
				text: this.status.label,
				variant: "filled"
			};
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
