import Topbar from "./Topbar.vue";

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
        { link: "https://getkirby.com", text: "Home" },
        { link: "https://getkirby.com/docs", text: "Docs" },
        { link: "https://getkirby.com/docs/guide", text: "Guide" },
        { link: "https://getkirby.com/docs/guide/blueprints", text: "Blueprints" }
      ];
    }
  },
  template: `
    <div>
      <k-topbar
        :breadcrumb="breadcrumb"
        :loading="loading"
        class="mb-6"
      />

      <k-view class="px-6">
        <k-input type="toggle" v-model="loading" text="Loading" />
      </k-view>
    </div>
  `,
});

