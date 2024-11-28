<template>
	<div class="k-user-profile">
		<k-user-avatar :disabled="isLocked" :model="model" />

		<k-button-group
			:buttons="[
				{
					icon: 'email',
					text: `${model.email}`,
					title: `${$t('email')}: ${model.email}`,
					disabled: !canChangeEmail,
					click: () => $dialog(model.link + '/changeEmail')
				},
				{
					icon: 'bolt',
					text: `${model.role}`,
					title: `${$t('role')}: ${model.role}`,
					disabled: !canChangeRole,
					click: () => $dialog(model.link + '/changeRole')
				},
				{
					icon: 'translate',
					text: `${model.language}`,
					title: `${$t('language')}: ${model.language}`,
					disabled: !canChangeLanguage,
					click: () => $dialog(model.link + '/changeLanguage')
				}
			]"
		/>
	</div>
</template>

<script>
/**
 * @since 4.0.0
 * @internal
 */
export default {
	props: {
		canChangeEmail: Boolean,
		canChangeLanguage: Boolean,
		canChangeRole: Boolean,
		isLocked: Boolean,
		model: Object,
		/**
		 * @deprecated Will be remove in v5
		 */
		permissions: Object
	}
};
</script>

<style>
.k-user-profile {
	--button-height: auto;
	padding: var(--spacing-2);
	background: var(--color-white);
	border-radius: var(--rounded-lg);
	display: flex;
	align-items: center;
	gap: var(--spacing-3);
	box-shadow: var(--shadow);
}

.k-user-profile .k-button-group {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}
</style>
