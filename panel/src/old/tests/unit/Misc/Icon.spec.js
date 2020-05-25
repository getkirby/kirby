import { mount } from '@vue/test-utils'
import Icon from '@/components/Misc/Icon.vue'

describe("Icon.vue", () => {

  it("renders icon", () => {
    const wrapper = mount(Icon, {
      propsData: {
        type: "add"
      }
    });
    expect(wrapper.classes()).toContain('k-icon');
    expect(wrapper.contains("svg")).toBe(true);
    expect(wrapper.html()).toContain('xlink:href="#icon-add"');
  });

  it("renders emoji", () => {
    const wrapper = mount(Icon, {
      propsData: {
        emoji: true,
        type: "❤️"
      }
    });
    expect(wrapper.html()).toContain('<span class="k-icon-emoji">❤️</span>');
  });

  it("has background attribute", () => {
    const wrapper = mount(Icon, {
      propsData: {
        back: "black"
      }
    });
    expect(wrapper.attributes("data-back")).toBe("black");
  });

  it("has size attribute", () => {
    const wrapper = mount(Icon, {
      propsData: {
        size: "large"
      }
    });
    expect(wrapper.attributes("data-size")).toBe("large");
  });

  it("has the correct role", () => {
    const withAlt = mount(Icon, {
      propsData: {
        alt: "Some text"
      }
    });
    expect(withAlt.attributes("role")).toBe("img");

    const withoutAlt = mount(Icon);
    expect(withoutAlt.attributes("role")).toBe(undefined);
  });

  it("has the correct aria label", () => {
    const withAlt = mount(Icon, {
      propsData: {
        alt: "Some text"
      }
    });
    expect(withAlt.attributes("aria-label")).toBe("Some text");

    const withoutAlt = mount(Icon);
    expect(withoutAlt.attributes("aria-label")).toBe(undefined);
  });

});
