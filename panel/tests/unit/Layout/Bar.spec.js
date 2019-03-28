import { mount } from '@vue/test-utils'
import Bar from '@/components/Layout/Bar.vue'

describe("Bar.vue", () => {

  it("renders bar", () => {
    const wrapper = mount(Bar);
    expect(wrapper.classes()).toContain("k-bar");
  });

  it("renders left slot", () => {
    const wrapper = mount(Bar, {
      slots: {
        left: "left"
      }
    });

    expect(wrapper.contains('[data-position="left"]')).toBe(true);
  });

  it("renders left slot", () => {
    const wrapper = mount(Bar, {
      slots: {
        left: "left"
      }
    });

    expect(wrapper.contains('[data-position="left"]')).toBe(true);
    expect(wrapper.contains('[data-position="center"]')).toBe(false);
    expect(wrapper.contains('[data-position="right"]')).toBe(false);

    expect(wrapper.find(".k-bar-slot").text()).toBe("left");
  });

  it("renders center slot", () => {
    const wrapper = mount(Bar, {
      slots: {
        center: "center"
      }
    });

    expect(wrapper.contains('[data-position="left"]')).toBe(false);
    expect(wrapper.contains('[data-position="center"]')).toBe(true);
    expect(wrapper.contains('[data-position="right"]')).toBe(false);

    expect(wrapper.find(".k-bar-slot").text()).toBe("center");
  });

  it("renders right slot", () => {
    const wrapper = mount(Bar, {
      slots: {
        right: "right"
      }
    });

    expect(wrapper.contains('[data-position="left"]')).toBe(false);
    expect(wrapper.contains('[data-position="center"]')).toBe(false);
    expect(wrapper.contains('[data-position="right"]')).toBe(true);

    expect(wrapper.find(".k-bar-slot").text()).toBe("right");
  });

});
