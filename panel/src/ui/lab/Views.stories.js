import Topbar from "../storybook/Topbar.vue";
import items from "../storybook/Items.js";

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
    photography() {
      return items(10);
    },
    notes() {
      return items(4);
    }
  },
  template: `
    <div class="k-site-view">
      <k-topbar :breadcrumb="breadcrumb" />
      <k-view>
        <k-header>
          Site
          <k-button-group slot="left">
            <k-button icon="open">Open</k-button>
          </k-button-group>
        </k-header>
        <k-grid gutter="large" class="mb-12">
          <k-column width="1/2">
            <section>
              <k-headline class="mb-3">Photography</k-headline>
              <k-collection
                :items="photography"
                layout="cards"
              />
            </section>
          </k-column>
          <k-column width="1/2">
            <section>
              <k-headline class="mb-3">Notes</k-headline>
              <k-collection
                :items="notes"
                layout="list"
              />
            </section>
          </k-column>
        </k-grid>
      </k-view>
    </div>
  `
});


