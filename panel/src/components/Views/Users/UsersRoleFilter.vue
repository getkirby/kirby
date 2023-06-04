<template>
	<k-button-group>
		<k-dropdown>
			<k-button
				:dropdown="true"
				:responsive="true"
				:text="role ? role.title : $t('role.all')"
				icon="funnel"
				size="sm"
				variant="filled"
				@click="$refs.roles.toggle()"
			/>
			<k-dropdown-content
				ref="roles"
				:options="[
					{
						text: $t('role.all'),
						icon: 'bolt',
						link: '/users'
					},
					'-',
					...roles.map((role) => ({
						text: role.title,
						icon: 'bolt',
						link: '/users?role=' + role.id
					}))
				]"
				align="right"
			/>
		</k-dropdown>
	</k-button-group>
</template>

<script>
export default {
	props: {
		role: Object,
		roles: Array
	}
};
</script>
