<template>
	<k-panel-inside class="k-users-view">
		<k-header>
			{{ $t("view.users") }}

			<template #buttons>
				<k-button-group
					:buttons="[
						{
							disabled: $panel.permissions.users.create === false,
							text: $t('user.create'),
							icon: 'add',
							click: () => $dialog('users/create')
						}
					]"
					variant="filled"
					size="sm"
				/>
			</template>

			<template v-if="roles.length > 1" #right>
				<k-button-group>
					<k-dropdown>
						<k-button
							:responsive="true"
							:text="`${$t('role')}: ${role ? role.title : $t('role.all')}`"
							icon="funnel"
							size="sm"
							variant="filled"
							@click="$refs.roles.toggle()"
						/>
						<k-dropdown-content ref="roles" align="right">
							<k-dropdown-item icon="bolt" link="/users">
								{{ $t("role.all") }}
							</k-dropdown-item>
							<hr />
							<k-dropdown-item
								v-for="roleFilter in roles"
								:key="roleFilter.id"
								:link="'/users/?role=' + roleFilter.id"
								icon="bolt"
							>
								{{ roleFilter.title }}
							</k-dropdown-item>
						</k-dropdown-content>
					</k-dropdown>
				</k-button-group>
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
