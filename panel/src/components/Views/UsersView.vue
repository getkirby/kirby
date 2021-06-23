<template>
  <k-inside>
    <k-view class="k-users-view">
      <k-header>
        {{ $t('view.users') }}

        <template #left>
          <k-button-group>
            <k-button
              :disabled="$permissions.users.create === false"
              icon="add"
              @click="$refs.create.open()"
            >
              {{ $t('user.create') }}
            </k-button>
          </k-button-group>
        </template>

        <template #right>
          <k-button-group>
            <k-dropdown>
              <k-button
                :responsive="true"
                icon="funnel"
                @click="$refs.roles.toggle()"
              >
                {{ $t("role") }}: {{ role ? role.title : $t("role.all") }}
              </k-button>
              <k-dropdown-content ref="roles" align="right">
                <k-dropdown-item icon="bolt" link="/users">
                  {{ $t("role.all") }}
                </k-dropdown-item>
                <hr>
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

      <template v-if="users.data.length > 0">
        <k-collection
          :items="items"
          :pagination="users.pagination"
          @paginate="paginate"
          @action="action"
        />
      </template>
      <template v-else-if="users.pagination.total === 0">
        <k-empty icon="users">
          {{ $t("role.empty") }}
        </k-empty>
      </template>

      <k-user-create-dialog ref="create" @success="$reload" />
      <k-user-email-dialog ref="email" @success="$reload" />
      <k-user-language-dialog ref="language" @success="$reload" />
      <k-user-password-dialog ref="password" />
      <k-user-remove-dialog ref="remove" @success="$reload" />
      <k-user-rename-dialog ref="rename" @success="$reload" />
      <k-user-role-dialog ref="role" @success="$reload" />
    </k-view>
  </k-inside>
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
      return this.users.data.map(user => {
        user.options = async ready => {
          try {
            const options = await this.$api.users.options(user.id, "list")
            ready(options);

          } catch (error) {
            this.$store.dispatch("notification/error", error);
          }
        }

        return user;
      })
    }
  },
  methods: {
    action(action, user) {
      switch (action) {
        case "edit":
          this.$go("/users/" + user.id);
          break;
        case "email":
          this.$refs.email.open(user.id);
          break;
        case "role":
          this.$refs.role.open(user.id);
          break;
        case "rename":
          this.$refs.rename.open(user.id);
          break;
        case "password":
          this.$refs.password.open(user.id);
          break;
        case "language":
          this.$refs.language.open(user.id);
          break;
        case "remove":
          this.$refs.remove.open(user.id);
          break;
      }
    },
    paginate(pagination) {
      this.$go(window.location, {
        data: {
          page: pagination.page
        }
      });
    }
  }
};
</script>
