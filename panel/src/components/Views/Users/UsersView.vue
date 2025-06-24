<template>
	<k-panel-inside class="k-users-view">
		<k-header class="k-users-view-header">
			{{ $t("view.users") }}

			<template #buttons>
				<k-view-buttons :buttons="buttons" />
			</template>
		</k-header>
		<k-tabs :tab="role?.id ?? 'all'" :tabs="tabs" />
		<k-collection
			:empty="empty"
			:items="items"
			:pagination="users.pagination"
			@paginate="paginate"
		/>
	</k-panel-inside>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		buttons: Array,
		role: Object,
		roles: Array,
		search: String,
		title: String,
		users: Object
	},
	computed: {
		empty() {
			return {
				icon: "users",
				text: this.$t("role.empty")
			};
		},
		items() {
			return this.users.data.map((user) => {
				user.options = this.$dropdown(user.link);
				return user;
			});
		},
		tabs() {
			const roles = [
				{
					name: "all",
					label: this.$t("role.all"),
					link: "/users"
				}
			];

			for (const role of this.roles) {
				roles.push({
					name: role.id,
					label: role.title,
					link: "/users?role=" + role.id
				});
			}

			return roles;
		}
	},
	methods: {
		create() {
			this.$dialog("users/create", {
				query: {
					role: this.role?.id
				}
			});
		},
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

<style>
.k-users-view-header {
	margin-bottom: 0;
}
</style>
