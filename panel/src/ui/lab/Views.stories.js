import { action } from "@storybook/addon-actions";
import Topbar from "../storybook/Topbar.vue";
import Pages from "../storybook/Pages.js";
import Users from "../storybook/Users.js";

export default {
  title: "Lab | Views",
};

export const SiteView = () => ({
  components: {
    "k-topbar": Topbar
  },
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
              layout: "cards"
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
    <div class="k-site-view">
      <k-topbar :breadcrumb="breadcrumb" />
      <k-view>
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
    </div>
  `
});



export const UsersView = () => ({
  components: {
    "k-topbar": Topbar
  },
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
    <div class="k-users-view">
      <k-topbar :breadcrumb="breadcrumb" />
      <k-view>
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
    </div>
  `
});


