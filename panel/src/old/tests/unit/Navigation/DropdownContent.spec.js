import { mount, createLocalVue } from "@vue/test-utils";
import Vue from "vue";
import DropdownContent from "@/components/Navigation/DropdownContent.vue";
import DropdownItem from "@/components/Navigation/DropdownItem.vue";
import Button from "@/components/Navigation/Button.vue";
import ButtonNative from "@/components/Navigation/ButtonNative.vue";

Vue.component("k-dropdown-item", DropdownItem);
Vue.component("k-button", Button);
Vue.component("k-button-native", ButtonNative);

const localVue = createLocalVue();

localVue.prototype.$events = {
  $on() {},
  $off() {},
};

describe("DropdownContent.vue", () => {

  it("open/close", () => {
    const wrapper = mount(DropdownContent, {
      slots: {
        default: "content"
      },
      localVue,
    });

    // closed dropdown
    expect(wrapper.html()).toBe(undefined);

    // open dropdown
    wrapper.vm.open();

    expect(wrapper.classes()).toContain("k-dropdown-content");
    expect(wrapper.text()).toBe("content");

    // close it again
    wrapper.vm.close();

    expect(wrapper.html()).toBe(undefined);
  });

  it("align", () => {
    const wrapper = mount(DropdownContent, {
      propsData: {
        align: "right"
      },
      localVue,
    });

    // open it first to render the content
    wrapper.vm.open();

    expect(wrapper.attributes("data-align")).toBe("right");
  });

  it("items", () => {
    const wrapper = mount(DropdownContent, {
      propsData: {
        options: [
          {
            text: "Item A",
          },
          {
            text: "Item B"
          }
        ]
      },
      localVue,
    });

    // open it first to render the content
    wrapper.vm.open();

    expect(wrapper.find(".k-dropdown-item:first-child").text()).toBe("Item A");
    expect(wrapper.find(".k-dropdown-item:last-child").text()).toBe("Item B");
  });

});
