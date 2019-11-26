import { mount } from "@vue/test-utils";
import Vue from "vue";
import PrevNext from "@/components/Navigation/PrevNext.vue";
import Link from "@/components/Navigation/Link.vue";
import Button from "@/components/Navigation/Button.vue";
import ButtonDisabled from "@/components/Navigation/ButtonDisabled.vue";
import ButtonLink from "@/components/Navigation/ButtonLink.vue";
import ButtonGroup from "@/components/Navigation/ButtonGroup.vue";
import Icon from "@/components/Misc/Icon.vue";

Vue.component("k-link", Link);
Vue.component("k-button", Button);
Vue.component("k-button-disabled", ButtonDisabled);
Vue.component("k-button-link", ButtonLink);
Vue.component("k-button-group", ButtonGroup);
Vue.component("k-icon", Icon);

describe("PrevNext.vue", () => {

  it("renders", () => {
    const wrapper = mount(PrevNext);

    expect(wrapper.is('.k-button-group')).toBe(true);
    expect(wrapper.is('.k-prev-next')).toBe(true);
    expect(wrapper.find('.k-button:first-child').is('span')).toBe(true);
    expect(wrapper.find('.k-button:last-child').is('span')).toBe(true);
  });

  it("prev", () => {
    const wrapper = mount(PrevNext, {
      propsData: {
        prev: { link: "/prev" }
      }
    });

    expect(wrapper.find(".k-button:first-child").is("a")).toBe(true);
    expect(wrapper.find(".k-button:first-child").attributes("href")).toBe("/prev");
    expect(wrapper.find(".k-button:last-child").is("span")).toBe(true);
  });

  it("next", () => {
    const wrapper = mount(PrevNext, {
      propsData: {
        next: { link: "/next" }
      }
    });

    expect(wrapper.find(".k-button:first-child").is("span")).toBe(true);
    expect(wrapper.find(".k-button:last-child").is("a")).toBe(true);
    expect(wrapper.find(".k-button:last-child").attributes("href")).toBe("/next");
  });

  it("prev/next", () => {
    const wrapper = mount(PrevNext, {
      propsData: {
        prev: { link: "/prev" },
        next: { link: "/next" }
      }
    });

    expect(wrapper.find(".k-button:first-child").is("a")).toBe(true);
    expect(wrapper.find(".k-button:first-child").attributes("href")).toBe("/prev");
    expect(wrapper.find(".k-button:last-child").is("a")).toBe(true);
    expect(wrapper.find(".k-button:last-child").attributes("href")).toBe("/next");
  });

});
