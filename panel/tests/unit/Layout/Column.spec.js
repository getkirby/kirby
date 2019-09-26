import { mount } from '@vue/test-utils'
import Column from '@/components/Layout/Column.vue'

describe("Column.vue", () => {

  it("renders a column", () => {
    const wrapper = mount(Column);
    expect(wrapper.classes()).toContain("k-column");
    expect(wrapper.attributes("data-width")).toBe(undefined);
  });

  it("renders the default slot", () => {
    const wrapper = mount(Column, {
      slots: {
        default: "content"
      }
    });

    expect(wrapper.text()).toBe("content");
  });

  it("adds the width attribute", () => {
    const wrapper = mount(Column, {
      propsData: {
        width: "1/2"
      }
    });

    expect(wrapper.attributes("data-width")).toBe("1/2");
  });

});
