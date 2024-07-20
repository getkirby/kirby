<template>
	<k-view-button
		v-if="$panel.view.component === 'k-users-view'"
		:disabled="!$panel.permissions.users.create"
		:text="$t('user.create')"
		icon="add"
		@click="
			$dialog('users/create', {
				query: {
					role: $panel.view.props.role?.id
				}
			})
		"
	/>
</template>
