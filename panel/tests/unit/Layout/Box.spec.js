import { mount } from '@vue/test-utils'
import Box from '@/components/Layout/Box.vue'

describe("Box.vue", () => {

  it("renders box", () => {
    const wrapper = mount(Box);
    expect(wrapper.classes()).toContain("k-box");
  });

  it("renders box with text", () => {
    const wrapper = mount(Box, {
      propsData: {
        text: "test"
      }
    });
    expect(wrapper.text()).toBe("test");
  });

  it("renders box with default slot", () => {
    const wrapper = mount(Box, {
      slots: {
        default: "test"
      }
    });
    expect(wrapper.text()).toBe("test");
  });

});
