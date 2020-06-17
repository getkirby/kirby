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
      <k-collection
        :empty="{
          icon: 'users',
          text: $t('role.empty')
        }"
        :items="users"
        :loader="{ info: true }"
        :loading="loading"
        layout="cardlets"
        @option="onOption"
        @paginate="onPaginate"
      />

      <!-- dialogs -->
      <k-user-create-dialog
        ref="createDialog"
        @success="reload"
      />
      <k-user-email-dialog
        ref="emailDialog"
        @success="reload"
      />
      <k-user-language-dialog
        ref="languageDialog"
        @success="reload"
      />
      <k-user-password-dialog
        ref="passwordDialog"
      />
      <k-user-remove-dialog
        ref="removeDialog"
        @success="reload"
      />
      <k-user-rename-dialog
        ref="renameDialog"
        @success="reload"
      />
      <k-user-role-dialog
        ref="roleDialog"
        @success="reload"
      />
    </k-view>
  </k-inside>
</template>

<script>
export default {
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    role: String,
    roles: {
      type: Array,
      default() {
        return [{
          name: "admin",
          title: this.$t("role.admin.title")
        }];
      }
    },
    users: {
      type: Array,
      default() {
        return [];
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
    }
  },
  methods: {
    onChangeRole(option) {
      this.$emit("role", option.id);
    },
    onOption(option, user) {
      this.$refs[option + "Dialog"].open(user.id);
    },
    onPaginate(pagination) {
      this.$emit("paginate", pagination);
    },
    reload() {
      this.$emit("reload");
    }
  }
};
</script>
