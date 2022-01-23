import { describe, expect, it } from "vitest";
import { mount } from "@vue/test-utils";
import Progress from "./Progress.vue";

describe.concurrent("Progress.vue", () => {
  it("mounts component", async () => {
    const wrapper = mount(Progress);
    expect(wrapper.element.value).toBe(0);
  });

  it("has/sets value", async () => {
    const wrapper = mount(Progress, {
      propsData: {
        value: 30
      }
    });

    expect(wrapper.element.value).toBe(30);
    await wrapper.setProps({ value: 50 });
    expect(wrapper.element.value).toBe(50);
    await wrapper.vm.set(70);
    expect(wrapper.element.value).toBe(70);

    expect(() => {
      wrapper.vm.set(110);
    }).toThrow("value has to be between 0 and 100");
  });

  it("has CSS selector", async () => {
    const wrapper = mount(Progress);
    expect(wrapper.classes()).toContain("k-progress");
  });

  it("matches snapshot", async () => {
    const wrapper = mount(Progress, {
      propsData: {
        value: 30
      }
    });

    expect(wrapper.element).toMatchSnapshot();
  });
});
