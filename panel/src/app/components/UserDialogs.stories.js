import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "App | Dialogs / User Dialogs",
  decorators: [Padding],
};

export const regular = () => ({
  data() {
    return {
      users: [],
    };
  },
  created() {
    this.load();
  },
  methods: {
    async load() {
      this.users = await this.$api.users.list();
    },
    open() {
      this.$refs.dialog.open();
    },
    onOption(option, user) {
      this.$refs[option + "Dialog"].open(user.id);
    }
  },
  template: `
    <div>
      <k-header-bar
        :options="[
          { icon: 'add', text: 'Add user' }
        ]"
        text="Users"
        @option="$refs.createDialog.open()"
      />
      <ul class="mb-8">
        <li
          v-for="user in users.data"
          :key="user.id"
          class="bg-white mb-2px shadow rounded-sm flex items-center justify-between"
        >
          <span class="p-3">{{ user.email }}</span>
          <k-options-dropdown
            :options="[
              { icon: 'title', text: 'Change name', option: 'changeName' },
              { icon: 'email', text: 'Change email', option: 'changeEmail' },
              { icon: 'key', text: 'Change password', option: 'changePassword' },
              { icon: 'globe', text: 'Change language', option: 'changeLanguage' },
              { icon: 'bolt', text: 'Change role', option: 'changeRole' },
              { icon: 'trash', text: 'Delete user', option: 'remove' },
            ]"
            @option="onOption($event, user)"
          />
        </li>
      </ul>
      <k-user-create-dialog ref="createDialog" @success="load" />
      <k-user-rename-dialog ref="changeNameDialog" @success="load" />
      <k-user-email-dialog ref="changeEmailDialog" @success="load" />
      <k-user-password-dialog ref="changePasswordDialog" @success="load" />
      <k-user-language-dialog ref="changeLanguageDialog" @success="load" />
      <k-user-role-dialog ref="changeRoleDialog" @success="load" />
      <k-user-remove-dialog ref="removeDialog" @success="load" />

      <k-headline class="mb-3">DB</k-headline>
      <k-code-block :code="users.data" />
    </div>
  `,
});

