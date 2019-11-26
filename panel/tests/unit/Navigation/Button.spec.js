import { mount } from "@vue/test-utils";
import Vue from "vue";
import Button from "@/components/Navigation/Button.vue";
import ButtonDisabled from "@/components/Navigation/ButtonDisabled.vue";
import ButtonLink from "@/components/Navigation/ButtonLink.vue";
import ButtonNative from "@/components/Navigation/ButtonNative.vue";
import Icon from "@/components/Misc/Icon.vue";

Vue.component("k-button-disabled", ButtonDisabled);
Vue.component("k-button-link", ButtonLink);
Vue.component("k-button-native", ButtonNative);
Vue.component("k-icon", Icon);

describe("Button.vue", () => {

  it("renders text", () => {
    const text = "text";
    const wrapper = mount(Button, {
      slots: {
        default: text
      }
    });
    expect(wrapper.text()).toMatch(text);
  });

  it("renders icon", () => {
    const wrapper = mount(Button, {
      propsData: {
        icon: "add"
      }
    })
    expect(wrapper.contains(Icon)).toBe(true);
  });

  it("renders icon and text", () => {
    const icon = "add";
    const text = "text";
    const wrapper = mount(Button, {
      propsData: {
        icon: icon
      },
      slots: {
        default: text
      }
    })

    expect(wrapper.contains(Icon)).toBe(true);
    expect(wrapper.text()).toMatch(text);
  });

});
