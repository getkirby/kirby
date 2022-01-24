import { describe, expect, it } from "vitest";
import { mount } from "@vue/test-utils";
import PrevNext from "./PrevNext.vue";

const setup = {
  stubs: ["k-button-group"]
};

describe.concurrent("PrevNext.vue", () => {
  it("has buttons", async () => {
    const wrapper = mount(PrevNext, {
      propsData: {
        prev: {
          link: "https://getkirby.com"
        }
      },
      ...setup
    });

    expect(wrapper.vm.buttons).toEqual([
      { link: "https://getkirby.com", icon: "angle-left" },
      { disabled: true, link: "#", icon: "angle-right" }
    ]);
    expect(wrapper.findComponent("k-button-group-stub").exists()).toBe(true);
    expect(wrapper.element).toMatchSnapshot();
  });

  it("has `button()` method", async () => {
    const wrapper = mount(PrevNext, setup);

    const defaults = {
      disabled: true,
      link: "#"
    };

    const config = {
      link: "https://getkirby.com"
    };

    expect(wrapper.vm.button()).toEqual(defaults);
    expect(wrapper.vm.button()).toEqual(defaults);
    expect(wrapper.vm.button(config)).toEqual(config);
  });

  it("has CSS selector", async () => {
    const wrapper = mount(PrevNext, setup);
    expect(wrapper.classes()).toContain("k-prev-next");
  });
});
