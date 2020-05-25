import { mount } from '@vue/test-utils'
import Grid from '@/components/Layout/Grid.vue'

describe("Grid.vue", () => {

  it("renders", () => {
    const wrapper = mount(Grid);
    expect(wrapper.classes()).toContain("k-grid");
    expect(wrapper.attributes("data-gutter")).toBe(undefined);
  });

  it("renders the default slot", () => {
    const wrapper = mount(Grid, {
      slots: {
        default: "content"
      }
    });

    expect(wrapper.text()).toBe("content");
  });

  it("adds the gutter attribute", () => {
    const wrapper = mount(Grid, {
      propsData: {
        gutter: "large"
      }
    });

    expect(wrapper.attributes("data-gutter")).toBe("large");
  });

});
