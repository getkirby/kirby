import { mount } from '@vue/test-utils';
import Vue from "vue";
import ButtonGroup from '@/components/Navigation/ButtonGroup.vue';

describe("ButtonGroup.vue", () => {

  it("renders", () => {
    const wrapper = mount(ButtonGroup, {
      slots: {
        default: "buttons"
      }
    });
    expect(wrapper.classes()).toContain("k-button-group");
    expect(wrapper.text()).toBe("buttons");
  });

});
