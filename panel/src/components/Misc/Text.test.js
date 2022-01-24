import { describe, expect, it } from "vitest";
import { mount } from "@vue/test-utils";
import Text from "./Text.vue";

describe.concurrent("Text.vue", () => {
  it("has default slot", async () => {
    const wrapper = mount(Text, {
      slots: {
        default: "Foo"
      }
    });

    expect(wrapper.text()).toBe("Foo");
  });

  it("has attributes", async () => {
    const wrapper = mount(Text, {
      propsData: {
        align: "right",
        size: "small",
        theme: "help"
      }
    });

    expect(wrapper.attributes("data-align")).toBe("right");
    expect(wrapper.attributes("data-size")).toBe("small");
    expect(wrapper.attributes("data-theme")).toBe("help");
  });

  it("has CSS selector", async () => {
    const wrapper = mount(Text);
    expect(wrapper.classes()).toContain("k-text");
  });

  it("matches snapshot", async () => {
    const wrapper = mount(Text, {
      propsData: {
        align: "right",
        size: "large",
        theme: "help"
      },
      slots: {
        default: "This is some text"
      }
    });

    expect(wrapper.element).toMatchSnapshot();
  });
});
