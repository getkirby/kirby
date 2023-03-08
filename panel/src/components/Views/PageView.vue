<template>
	<k-inside
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
			:tab="tab.name"
			:tabs="tabs"
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
					<k-status-icon
						v-if="status"
						:status="model.status"
						:disabled="!permissions.changeStatus || isLocked"
						:responsive="true"
						:text="status.label"
						variant="filled"
						size="sm"
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
			</template>
		</k-header>
		<k-sections
			:blueprint="blueprint"
			:empty="$t('page.blueprint', { blueprint: $esc(blueprint) })"
			:lock="lock"
			:parent="id"
			:tab="tab"
		/>
		<template #footer>
			<k-form-buttons :lock="lock" />
		</template>
	</k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
	extends: ModelView,
	props: {
		status: Object
	},
	computed: {
		protectedFields() {
			return ["title"];
		}
	}
};
</script>
