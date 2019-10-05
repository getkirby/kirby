import { mount } from '@vue/test-utils'
import View from '@/components/Layout/View.vue'

describe("View.vue", () => {

  it("renders", () => {
    const wrapper = mount(View);
    expect(wrapper.classes()).toContain("k-view");
    expect(wrapper.attributes("data-align")).toBe(undefined);
  });

  it("content", () => {
    const wrapper = mount(View, {
      slots: {
        default: "content"
      }
    });

    expect(wrapper.text()).toBe("content");
  });

  it("align", () => {
    const wrapper = mount(View, {
      propsData: {
        align: "right"
      }
    });

    expect(wrapper.attributes("data-align")).toBe("right");
  });

});
