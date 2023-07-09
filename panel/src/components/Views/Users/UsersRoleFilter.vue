<template>
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
					current: !role,
					icon: 'bolt',
					link: '/users',
					text: $t('role.all')
				},
				'-',
				...roles.map((roleFilter) => ({
					current: role && role.id === roleFilter.id,
					icon: 'bolt',
					link: '/users?role=' + roleFilter.id,
					text: roleFilter.title
				}))
			]"
			align-x="end"
		/>
	</k-dropdown>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		role: Object,
		roles: Array
	}
};
</script>
