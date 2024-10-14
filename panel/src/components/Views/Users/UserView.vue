<template>
	<k-panel-inside
		:data-has-tabs="tabs.length > 1"
		:data-id="model.id"
		:data-locked="isLocked"
		:data-template="blueprint"
		class="k-user-view"
	>
		<template #topbar>
			<k-prev-next :prev="prev" :next="next" />
		</template>

		<k-header
			:editable="canChangeName"
			class="k-user-view-header"
			@edit="$dialog(id + '/changeName')"
		>
			<span
				v-if="!model.name || model.name.length === 0"
				class="k-user-name-placeholder"
			>
				{{ $t("name") }} …
			</span>
			<template v-else>
				{{ model.name }}
			</template>

			<template #buttons>
				<k-button-group>
					<k-button
						:disabled="isLocked"
						:dropdown="true"
						:title="$t('settings')"
						icon="cog"
						size="sm"
						variant="filled"
						class="k-user-view-options"
						@click="$refs.settings.toggle()"
					/>
					<k-dropdown-content
						ref="settings"
						align-x="end"
						:options="$dropdown(id)"
					/>
					<k-languages-dropdown />
				</k-button-group>

				<k-form-buttons :lock="lock" />
			</template>
		</k-header>

		<k-user-profile
			:can-change-email="canChangeEmail"
			:can-change-language="canChangeLanguage"
			:can-change-name="canChangeName"
			:can-change-role="canChangeRole"
			:is-locked="isLocked"
			:model="model"
			:permissions="permissions"
		/>

		<k-model-tabs :tab="tab.name" :tabs="tabs" />

		<k-sections
			:blueprint="blueprint"
			:empty="$t('user.blueprint', { blueprint: $esc(blueprint) })"
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
		canChangeEmail: Boolean,
		canChangeLanguage: Boolean,
		canChangeName: Boolean,
		canChangeRole: Boolean
	}
};
</script>

<style>
.k-user-name-placeholder {
	color: var(--color-gray-500);
	transition: color 0.3s;
}
.k-user-view-header[data-editable="true"] .k-user-name-placeholder:hover {
	color: var(--color-gray-900);
}
.k-user-view-header {
	margin-bottom: 0;
	border-bottom: 0;
}
.k-user-view .k-user-profile {
	margin-bottom: var(--spacing-12);
}
/** .k-user-view:has(.k-tabs) .k-user-profile */
.k-user-view[data-has-tabs="true"] .k-user-profile {
	margin-bottom: 0;
}
</style>
