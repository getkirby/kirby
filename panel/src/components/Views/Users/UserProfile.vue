<template>
	<div class="k-user-profile">
		<k-user-avatar :id="id" :api="api" :avatar="avatar" :is-locked="isLocked" />

		<k-button-group
			:buttons="[
				{
					icon: 'email',
					text: email,
					title: `${$t('email')}: ${email}`,
					disabled: !canChangeEmail,
					click: () => $dialog(api + '/changeEmail')
				},
				{
					icon: 'bolt',
					text: role,
					title: `${$t('role')}: ${role}`,
					disabled: !canChangeRole,
					click: () => $dialog(api + '/changeRole')
				},
				{
					icon: 'translate',
					text: language,
					title: `${$t('language')}: ${language}`,
					disabled: !canChangeLanguage,
					click: () => $dialog(api + '/changeLanguage')
				}
			]"
		/>
	</div>
</template>

<script>
/**
 * @since 4.0.0
 * @unstable
 */
export default {
	props: {
		api: String,
		avatar: String,
		canChangeEmail: Boolean,
		canChangeLanguage: Boolean,
		canChangeRole: Boolean,
		email: String,
		id: String,
		isLocked: Boolean,
		language: String,
		role: String
	}
};
</script>

<style>
.k-user-profile {
	--button-height: auto;
	padding: var(--spacing-2);
	background: var(--item-color-back);
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
