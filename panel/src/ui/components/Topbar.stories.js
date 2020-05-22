import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Navigation / Topbar",
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      loading: false
    }
  },
  computed: {
    breadcrumb() {
      return [
        {
          icon: "page",
          link: "site",
          text: "Maegazine"
        },
        {
          link: "pages/photography",
          text: "Photography"
        },
        {
          link: "pages/photography+trees",
          text: "Trees"
        },
      ];
    },
    menu() {
      return [
        {
          link: 'site',
          icon: 'page',
          text: 'Site'
        },
        {
          link: 'users',
          icon: 'users',
          text: 'Users',
          current: 'page',
          color: 'blue'
        },
        {
          link: 'settings',
          icon: 'settings',
          text: 'Settings'
        },
        '-',
        {
          link: 'account',
          icon: 'account',
          text: 'Your account'
        },
        '-',
        {
          link: 'logout',
          icon: 'logout',
          text: 'Logout'
          },
      ];
    }
  },
  methods: {
    onSearch: action("search")
  },
  template: `
    <div>
      <k-topbar
        :breadcrumb="breadcrumb"
        :loading="loading"
        :menu="menu"
        class="mb-6"
        @search="onSearch"
      />
      <k-view>
        <k-headline class="mb-2">Controls</k-headline>
        <k-input type="toggle" v-model="loading" text="Loading state" />
      </k-view>
    </div>
  `,
});

export const noBreadcrumb = () => ({
  extends: regular(),
  computed: {
    breadcrumb() {
      return;
    }
  }
});

export const options = () => ({
  extends: regular(),
  template: `
    <div>
      <k-topbar
        :breadcrumb="breadcrumb"
        :loading="loading"
        :menu="menu"
        class="mb-6"
        @search="onSearch"
      >
        <template v-slot:option>
          <k-dropdown>
            <k-button
              class="k-topbar-button"
              color="orange-light"
              icon="edit"
              @click="$refs.changes.toggle()"
            />
            <k-dropdown-content
              ref="changes"
              align="right"
              theme="light"
            >
              <k-dropdown-item icon="page">Some page</k-dropdown-item>
              <k-dropdown-item icon="image">some-file.jpg</k-dropdown-item>
            </k-dropdown-content>
          </k-dropdown>
        </template>
      </k-topbar>
      <k-view>
        <k-headline class="mb-2">Controls</k-headline>
        <k-input type="toggle" v-model="loading" text="Loading state" />
      </k-view>
    </div>
  `,
});
