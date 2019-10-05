import { mount } from "@vue/test-utils";
import Vue from "vue";
import DropdownItem from "@/components/Navigation/DropdownItem.vue";
import Button from "@/components/Navigation/Button.vue";
import Link from "@/components/Navigation/Link.vue";
import Icon from "@/components/Misc/Icon.vue";

Vue.component("k-button", Button);
Vue.component("k-link", Link);
Vue.component("k-icon", Icon);

describe("DropdownItem.vue", () => {

  it("button", () => {
    const wrapper = mount(DropdownItem, {
      slots: {
        default: "test"
      }
    });

    expect(wrapper.element.tagName).toBe("BUTTON");
    expect(wrapper.contains(".k-icon")).toBe(false);
    expect(wrapper.classes()).toContain("k-dropdown-item");
    expect(wrapper.classes()).toContain("k-button");
    expect(wrapper.text()).toBe("test");
  });

  it("icon", () => {
    const wrapper = mount(DropdownItem, {
      slots: {
        default: "test"
      },
      propsData: {
        icon: "url"
      }
    });

    expect(wrapper.contains(".k-icon")).toBe(true);
  });

  it("link", () => {
    const wrapper = mount(DropdownItem, {
      propsData: {
        link: "https://getkirby.com"
      }
    });

    expect(wrapper.element.tagName).toBe("A");
    expect(wrapper.find("a").attributes("href")).toBe("https://getkirby.com");
  });

  it("disabled button", () => {
    const wrapper = mount(DropdownItem, {
      propsData: {
        disabled: true
      }
    });

    expect(wrapper.element.tagName).toBe("BUTTON");
    expect(wrapper.attributes("disabled")).toBe("disabled");
  });

  it("disabled link", () => {
    const wrapper = mount(DropdownItem, {
      propsData: {
        link: "https://getkirby.com",
        disabled: true
      }
    });

    expect(wrapper.element.tagName).toBe("SPAN");
  });

  it("theme", () => {
    const wrapper = mount(DropdownItem, {
      propsData: {
        theme: "dark"
      }
    });
    expect(wrapper.attributes("data-theme")).toBe("dark");
  });

  it("current", () => {
    const wrapper = mount(DropdownItem, {
      propsData: {
        current: true
      }
    });
    expect(wrapper.attributes("aria-current")).toBe("true");
  });

});
