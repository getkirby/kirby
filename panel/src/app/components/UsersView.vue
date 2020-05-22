<template>
  <k-inside
    :breadcrumb="breadcrumb"
    class="k-users-view"
    search="users"
    view="users"
  >
    <k-view>
      <!-- header -->
      <k-header>
        {{ $t('view.users') }}
        <template v-slot:left>
          <k-button-group>
            <k-button
              :responsive="true"
              icon="add"
              @click="$refs.create.open()"
            >
              {{ $t('user.create') }}
            </k-button>
          </k-button-group>
        </template>

        <template v-slot:right>
          <k-select-dropdown
            :options="roleFilters"
            align="right"
            icon="funnel"
            before="Role:"
            @change="onChangeRole"
          />
        </template>
      </k-header>

      <!-- user cardlets -->
      <k-async-collection
        :empty="{
          icon: 'users',
          text: $t('role.empty')
        }"
        :items="users"
        layout="cardlets"
      />

      <!-- dialogs -->
      <k-user-create-dialog
        ref="create"
        @success="fetch"
      />
      <k-user-email-dialog
        ref="email"
        @success="fetch"
      />
      <k-user-language-dialog
        ref="language"
        @success="fetch"
      />
      <k-user-password-dialog
        ref="password"
      />
      <k-user-remove-dialog
        ref="remove"
        @success="fetch"
      />
      <k-user-rename-dialog
        ref="rename"
        @success="fetch"
      />
      <k-user-role-dialog
        ref="role"
        @success="fetch"
      />
    </k-view>
  </k-inside>
</template>

<script>
export default {
  props: {
    role: String,
    roles: {
      type: Array,
      default() {
        return [{
          name: "admin",
          title: this.$t("role.admin.title")
        }];
      }
    }
  },
  computed: {
    breadcrumb() {
      return [
        {
          icon: "users",
          text: this.$t("view.users")
        }
      ]
    },
    roleFilters() {
      let options = [
        {
          current: this.role === null,
          icon: "bolt",
          id: null,
          text: this.$t("role.all"),
        },
        "-"
      ];

      this.roles.forEach(role => {
        options.push({
          current: this.role === role.name,
          icon: "bolt",
          id: role.name,
          text: role.title,
        });
      });

      return options;
    },
    users() {
      return async ({ page, limit }) => {
        return await this.$model.users.list({
          page: page,
          limit: limit,
          role: this.role
        });
      }
    }
  },
};
</script>
