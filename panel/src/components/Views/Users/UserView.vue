<template>
	<k-panel-inside
		:data-id="id"
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
			@edit="$dialog(api + '/changeName')"
		>
			<span
				v-if="!name || name.length === 0"
				class="k-header-title-placeholder k-user-name-placeholder"
			>
				{{ $t("name") }} …
			</span>
			<template v-else>
				{{ name }}
			</template>

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

		<k-user-profile
			:id="id"
			:api="api"
			:avatar="avatar"
			:email="email"
			:can-change-email="canChangeEmail"
			:can-change-language="canChangeLanguage"
			:can-change-name="canChangeName"
			:can-change-role="canChangeRole"
			:is-locked="isLocked"
			:language="language"
			:role="role"
		/>

		<k-model-tabs :diff="diff" :tab="tab.name" :tabs="tabs" />

		<k-sections
			:blueprint="blueprint"
			:content="content"
			:empty="$t('user.blueprint', { blueprint: $esc(blueprint) })"
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
		avatar: String,
		canChangeEmail: Boolean,
		canChangeLanguage: Boolean,
		canChangeName: Boolean,
		canChangeRole: Boolean,
		email: String,
		language: String,
		name: String,
		role: String
	}
};
</script>

<style>
.k-user-view-header {
	margin-bottom: 0;
	border-bottom: 0;
}
.k-user-view .k-user-profile {
	margin-bottom: var(--spacing-12);
}
.k-user-view .k-user-profile:has(+ .k-tabs) {
	margin-bottom: 0;
}
</style>
