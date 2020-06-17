export default {
  title: "App | Separation / Inside"
};

export const unregistered = () => ({
  template: `
    <k-inside>
      <k-view class="py-6">
        Inside the Panel
      </k-view>
    </k-inside>
  `,
});

export const registered = () => ({
  created() {
    this.$store.dispatch("system/set", {
      license: "K3-test",
    });
  },
  destroyed() {
    this.$store.dispatch("system/set", {
      license: null,
    });
  },
  template: `
    <k-inside>
      <k-view class="py-6">
        Inside the Panel
      </k-view>
    </k-inside>
  `,
});

export const loading = () => ({
  created() {
    this.$store.dispatch("isLoading", true);
  },
  destroyed() {
    this.$store.dispatch("isLoading", false);
  },
  template: `
    <k-inside
      :registered="true"
    >
      <k-view class="py-6">
        Inside the Panel
      </k-view>
    </k-inside>
  `,
});

export const breadcrumb = () => ({
  computed: {
    breadcrumb() {
      return [
        { text: "Photography", link: "/pages/photography" },
        { text: "Animals", link: "/pages/photography+animals" }
      ]
    },
  },
  template: `
    <k-inside
      :breadcrumb="breadcrumb"
      :registered="true"
    >
      <k-view class="py-6">
        Inside the Panel
      </k-view>
    </k-inside>
  `,
});
