<template>
	<k-panel-inside class="k-users-view">
		<k-header>
			{{ $t("view.users") }}

			<template #buttons>
				<k-users-role-filter
					v-if="roles.length > 1"
					:role="role"
					:roles="roles"
				/>
				<k-button
					v-if="$panel.permissions.users.create"
					:text="$t('user.create')"
					icon="add"
					size="sm"
					variant="filled"
				/>
			</template>
		</k-header>

		<k-collection
			v-if="users.data.length > 0"
			:items="items"
			:pagination="users.pagination"
			@paginate="paginate"
		/>
		<k-empty v-else-if="users.pagination.total === 0" icon="users">
			{{ $t("role.empty") }}
		</k-empty>
	</k-panel-inside>
</template>

<script>
export default {
	props: {
		role: Object,
		roles: Array,
		search: String,
		title: String,
		users: Object
	},
	computed: {
		items() {
			return this.users.data.map((user) => {
				user.options = this.$dropdown(user.link);
				return user;
			});
		}
	},
	methods: {
		paginate(pagination) {
			this.$reload({
				query: {
					page: pagination.page
				}
			});
		}
	}
};
</script>
