import Topbar from "./Topbar.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Navigation / Topbar",
  component: Topbar
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
        :menu="[
          { link: 'site', icon: 'page', text: 'Site' },
          { link: 'users', icon: 'users', text: 'Users' },
          { link: 'settings', icon: 'settings', text: 'Settings' },
          '-',
          { link: 'account', icon: 'account', text: 'Your account' },
          '-',
          { link: 'logout', icon: 'logout', text: 'Logout' },
        ]"
        class="mb-6"
        @search="onSearch"
      />

      <k-view class="px-6">
        <k-input type="toggle" v-model="loading" text="Loading" />
      </k-view>
    </div>
  `,
});

