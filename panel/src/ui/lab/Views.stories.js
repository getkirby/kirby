import { action } from "@storybook/addon-actions";
import Pages from "../storybook/Pages.js";
import Users from "../storybook/Users.js";

export default {
  title: "Lab | Views",
};

export const SiteView = () => ({
  computed: {
    breadcrumb() {
      return [
        { icon: "home", text: "Site" }
      ]
    },
    columns() {
      return [
        {
          width: "1/2",
          sections: {
            photography: {
              add: true,
              pages: async () => Pages(10),
              type: "pages",
              layout: "cards",
              image: {
                ratio: "3/2",
                cover: true
              }
            }
          },
        },
        {
          width: "1/2",
          sections: {
            notes: {
              add: true,
              pages: async () => Pages(7),
              type: "pages"
            },
            pages: {
              add: true,
              pages: async () => Pages(4),
              type: "pages"
            }
          }
        }
      ];
    }
  },
  methods: {
    onEdit: action("edit")
  },
  template: `
    <k-inside
      :breadcrumb="breadcrumb"
      :registered="true"
      view="site"
    >
      <k-view class="k-site-view">
        <k-header
          :editable="true"
          @edit="onEdit"
        >
          Site
          <k-button-group slot="left">
            <k-button icon="open" link="https://yourdomain.com">Open</k-button>
          </k-button-group>
        </k-header>
        <k-sections :columns="columns" />
      </k-view>
    </k-inside>
  `
});


export const SettingsView = () => ({
  computed: {
    breadcrumb() {
      return [
        { icon: "settings", text: "Settings" }
      ]
    },
  },
  template: `
    <k-inside
      :breadcrumb="breadcrumb"
      :registered="true"
      view="settings"
    >
      <k-view class="k-settings-view">
        <k-header>
          Settings
        </k-header>
      </k-view>
    </k-inside>
  `
});



export const UsersView = () => ({
  data() {
    return {
      role: null,
    };
  },
  computed: {
    breadcrumb() {
      return [
        { icon: "users", text: "Users" }
      ]
    },
    roles() {
      return [
        {
          id: null,
          text: "All",
          icon: "bolt",
          current: this.role === null
        },
        "-",
        {
          id: "admin",
          text: "Admin",
          icon: "bolt",
          current: this.role === "admin"
        },
        {
          id: "editor",
          text: "Editor",
          icon: "bolt",
          current: this.role === "editor"
        },
        {
          id: "client",
          text: "Client",
          icon: "bolt",
          current: this.role === "client"
        }
      ];
    },
    users() {
      return async () => {
        return Users(10);
      };
    }
  },
  methods: {
    onChangeRole(option) {
      this.role = option.id;
    },
  },
  template: `
    <k-inside
      :breadcrumb="breadcrumb"
      :registered="true"
      search="users"
      view="users"
    >
      <k-view class="k-users-view">
        <k-header>
          Users
          <k-button-group slot="left">
            <k-button icon="add">Add a new user</k-button>
          </k-button-group>
          <k-select-dropdown
            :options="roles"
            slot="right"
            align="right"
            icon="funnel"
            before="Role:"
            @change="onChangeRole"
          />
        </k-header>
        <k-async-collection
          :items="users"
          layout="cardlets"
        />
      </k-view>
    </k-inside>
  `
});


