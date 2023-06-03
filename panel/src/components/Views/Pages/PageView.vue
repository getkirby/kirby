<template>
	<k-panel-inside
		:data-locked="isLocked"
		:data-id="model.id"
		:data-template="blueprint"
		class="k-page-view"
	>
		<template #topbar>
			<k-prev-next v-if="model.id" :prev="prev" :next="next" />
		</template>

		<k-header
			:editable="permissions.changeTitle && !isLocked"
			@edit="$dialog(id + '/changeTitle')"
		>
			{{ model.title }}
			<template #buttons>
				<k-button-group>
					<k-button
						v-if="permissions.preview && model.previewUrl"
						:link="model.previewUrl"
						:responsive="true"
						:text="$t('open')"
						icon="open"
						target="_blank"
						variant="filled"
						size="sm"
						class="k-page-view-preview"
					/>
					<k-button
						v-if="status"
						v-bind="statusBtn"
						class="k-page-view-status"
						@click="$dialog(id + '/changeStatus')"
					/>
					<k-dropdown class="k-page-view-options">
						<k-button
							:disabled="isLocked === true"
							:dropdown="true"
							:responsive="true"
							:text="$t('settings')"
							icon="cog"
							variant="filled"
							size="sm"
							@click="$refs.settings.toggle()"
						/>
						<k-dropdown-content ref="settings" :options="$dropdown(id)" />
					</k-dropdown>

					<k-languages-dropdown />
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
.k-page-view-status {
	--button-color-icon: var(--theme-color-600);
	--button-color-back: hsla(0, 0%, 0%, 7%);
	--button-color-hover: hsla(0, 0%, 0%, 12%);
}
</style>
