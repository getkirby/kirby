<template>
	<div class="k-user-profile">
		<k-user-avatar :model="model" :aria-disabled="isLocked" />

		<k-button-group
			:buttons="[
				{
					icon: 'email',
					text: `${$t('email')}: ${model.email}`,
					disabled: !permissions.changeEmail || isLocked,
					click: () => $dialog(model.link + '/changeEmail')
				},
				{
					icon: 'bolt',
					text: `${$t('role')}: ${model.role}`,
					disabled: !permissions.changeRole || isLocked,
					click: () => $dialog(model.link + '/changeRole')
				},
				{
					icon: 'globe',
					text: `${$t('language')}: ${model.language}`,
					disabled: !permissions.changeLanguage || isLocked,
					click: () => $dialog(model.link + '/changeLanguage')
				}
			]"
		/>
	</div>
</template>

<script>
export default {
	props: {
		isLocked: Boolean,
		model: Object,
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
	margin-bottom: var(--spacing-6);
}

.k-user-profile .k-button-group {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}
</style>
