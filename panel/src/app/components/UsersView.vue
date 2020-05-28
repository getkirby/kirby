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
              :text="$t('user.create')"
              icon="add"
              @click="$refs.createDialog.open()"
            />
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
        @option="onOption"
      />

      <!-- dialogs -->
      <k-user-create-dialog
        ref="createDialog"
        @success="onSuccess"
      />
      <k-user-email-dialog
        ref="emailDialog"
        @success="onSuccess"
      />
      <k-user-language-dialog
        ref="languageDialog"
        @success="onSuccess"
      />
      <k-user-password-dialog
        ref="passwordDialog"
      />
      <k-user-remove-dialog
        ref="removeDialog"
        @success="onSuccess"
      />
      <k-user-rename-dialog
        ref="renameDialog"
        @success="onSuccess"
      />
      <k-user-role-dialog
        ref="roleDialog"
        @success="onSuccess"
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
      const role = this.roles.find(role => role.name === this.role);

      if (!role) {
        return [];
      }

      return [
        {
          label: this.$t("role") + ": " + role.title,
          link: "/users/" + role.name
        }
      ];
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
          color: this.role === role.name ? "blue-light" : "white",
          icon: "bolt",
          id: role.name,
          text: role.title,
        });
      });

      return options;
    },
    users() {
      return async ({ page, limit }) => {
        const response = await this.$api.users.list({
          page: page,
          limit: limit,
          role: this.role
        });

        return response.data.map(user => {
          return {
            id: user.id,
            info: user.role.title,
            link: this.$model.users.link(user.id),
            options: async (ready) => {
              return ready(await this.$model.users.options(user.id))
            },
            preview: {
              image: user.avatar.url,
              cover: true
            },
            title: user.name || user.email,
          }
        });
      }
    }
  },
  methods: {
    onOption(option, user) {
      this.$refs[option + "Dialog"].open(user.id);
    },
    onSuccess() {

    }
  }
};
</script>
