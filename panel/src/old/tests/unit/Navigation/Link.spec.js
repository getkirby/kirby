import { mount } from "@vue/test-utils";
import Vue from "vue";
import Link from "@/components/Navigation/Link.vue";
import Button from "@/components/Navigation/Button.vue";
import Icon from "@/components/Misc/Icon.vue";

Vue.component("k-button", Button);
Vue.component("k-icon", Icon);

describe("Link.vue", () => {

  it("renders", () => {
    const to = "https://getkirby.com";
    const content = "content";

    const wrapper = mount(Link, {
      slots: {
        default: content
      },
      propsData: {
        to: to,
      }
    });

    expect(wrapper.text()).toBe("content");
    expect(wrapper.classes()).toContain("k-link");
    expect(wrapper.attributes("href")).toBe(to);
    expect(wrapper.element.tagName).toBe("A");
  });

  it("without to", () => {
    const wrapper = mount(Link, {
      slots: {
        default: "content"
      }
    });

    expect(wrapper.text()).toBe("content");
    expect(wrapper.classes()).toContain("k-link");
    expect(wrapper.attributes("data-disabled")).toBe("");
    expect(wrapper.element.tagName).toBe("SPAN");
  });

  it("disabled", () => {
    const wrapper = mount(Link, {
      slots: {
        default: "content"
      },
      propsData: {
        to: "https://getkirby.com",
        disabled: true
      }
    });

    expect(wrapper.text()).toBe("content");
    expect(wrapper.classes()).toContain("k-link");
    expect(wrapper.attributes("data-disabled")).toBe("");
    expect(wrapper.element.tagName).toBe("SPAN");
  });

  it("rel", () => {
    const wrapper = mount(Link, {
      propsData: {
        to: "https://getkirby.com",
        rel: "nofollow"
      }
    });

    expect(wrapper.attributes("rel")).toBe("nofollow");
  });

  it("title", () => {
    const wrapper = mount(Link, {
      propsData: {
        to: "https://getkirby.com",
        title: "Test"
      }
    });

    expect(wrapper.attributes("title")).toBe("Test");
  });

  it("target", () => {
    const wrapper = mount(Link, {
      propsData: {
        to: "https://getkirby.com",
        target: "_blank"
      }
    });

    expect(wrapper.attributes("target")).toBe("_blank");
    expect(wrapper.attributes("rel")).toBe("noreferrer noopener");
  });

  it("tabindex", () => {
    const wrapper = mount(Link, {
      propsData: {
        to: "https://getkirby.com",
        tabindex: 5
      }
    });

    expect(wrapper.attributes("tabindex")).toBe("5");
  });

});
