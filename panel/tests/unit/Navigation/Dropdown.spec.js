import { mount } from '@vue/test-utils';
import Dropdown from '@/components/Navigation/Dropdown.vue';

describe("Dropdown.vue", () => {

  it("renders", () => {
    const wrapper = mount(Dropdown, {
      slots: {
        default: "dropdown"
      }
    });
    expect(wrapper.classes()).toContain("k-dropdown");
    expect(wrapper.text()).toBe("dropdown");
  });

});
