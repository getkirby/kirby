import Inside from "./Inside.vue";

export default {
  title: "App | Separation / Inside",
  component: Inside,
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
  template: `
    <k-inside :registered="true">
      <k-view class="py-6">
        Inside the Panel
      </k-view>
    </k-inside>
  `,
});

export const loading = () => ({
  template: `
    <k-inside
      :loading="true"
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
        { icon: "home", text: "Site", link: "site" }
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
