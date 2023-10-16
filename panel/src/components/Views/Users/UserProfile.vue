<template>
	<div class="k-user-profile">
		<k-user-avatar :model="model" :aria-disabled="isLocked" />

		<k-button-group
			:buttons="[
				{
					icon: 'email',
					text: `${model.email}`,
					title: `${$t('email')}: ${model.email}`,
					disabled: !permissions.changeEmail || isLocked,
					click: () => $dialog(model.link + '/changeEmail')
				},
				{
					icon: 'bolt',
					text: `${model.role}`,
					title: `${$t('role')}: ${model.role}`,
					disabled: !permissions.changeRole || isLocked,
					click: () => $dialog(model.link + '/changeRole')
				},
				{
					icon: 'translate',
					text: `${model.language}`,
					title: `${$t('language')}: ${model.language}`,
					disabled: !permissions.changeLanguage || isLocked,
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
	box-shadow: var(--shadow);
}

.k-user-profile .k-button-group {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}
</style>
