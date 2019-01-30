import { mount } from '@vue/test-utils'
import Text from '@/components/Misc/Text.vue'

describe("Text.vue", () => {

  it("renders correctly", () => {
    const wrapper = mount(Text, {
      slots: {
        default: "text"
      }
    });
    expect(wrapper.text()).toMatch("text");
    expect(wrapper.classes()).toContain("k-text");
  });

  it("has align attribute", () => {
    const wrapper = mount(Text, {
      propsData: {
        align: "center"
      }
    });
    expect(wrapper.attributes("data-align")).toBe("center");
  });

  it("has size attribute", () => {
    const wrapper = mount(Text, {
      propsData: {
        size: "large"
      }
    });
    expect(wrapper.attributes("data-size")).toBe("large");
  });

  it("has theme attribute", () => {
    const wrapper = mount(Text, {
      propsData: {
        theme: "dark"
      }
    });
    expect(wrapper.attributes("data-theme")).toBe("dark");
  });

});
