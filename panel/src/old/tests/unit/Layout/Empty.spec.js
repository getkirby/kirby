import { mount } from "@vue/test-utils";
import Vue from "vue";
import Empty from "@/components/Layout/Empty.vue";
import Icon from "@/components/Misc/Icon.vue";

Vue.component("k-icon", Icon);

describe("Empty.vue", () => {

  it("renders", () => {
    const wrapper = mount(Empty);
    expect(wrapper.classes()).toContain("k-empty");
    expect(wrapper.contains('.k-icon')).toBe(false);
    expect(wrapper.attributes("data-layout")).toBe("list");
  });

  it("renders content", () => {
    const wrapper = mount(Empty, {
      slots: {
        default: "content"
      }
    });

    expect(wrapper.text()).toBe("content");
  });

  it("layout", () => {
    const wrapper = mount(Empty, {
      propsData: {
        layout: "cards"
      }
    });

    expect(wrapper.attributes("data-layout")).toBe("cards");
  });

  it("icon", () => {
    const wrapper = mount(Empty, {
      propsData: {
        icon: "file"
      }
    });

    expect(wrapper.contains('.k-icon')).toBe(true);
  });

  it("clicks", () => {

    let clicked = 0;

    const wrapper = mount(Empty, {
      listeners: {
        click() {
          clicked++;
        }
      },
      propsData: {
        icon: "file"
      }
    });

    wrapper.trigger("click");
    wrapper.find("[data-layout]").trigger("click");
    wrapper.find("p").trigger("click");
    wrapper.find(".k-icon").trigger("click");

    expect(clicked).toBe(4);

  });

});
