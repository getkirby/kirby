import { mount } from '@vue/test-utils'
import Headline from '@/components/Misc/Headline.vue'
import Link from "@/components/Navigation/Link.vue";

describe("Headline.vue", () => {

  it("renders headline", () => {
    const wrapper = mount(Headline, {
      slots: {
        default: "Headline"
      }
    });
    expect(wrapper.text()).toMatch("Headline");
    expect(wrapper.classes()).toContain("k-headline");
  });

  it("renders the correct tag", () => {

    // default: h2
    const h2 = mount(Headline);

    expect(h2.element.tagName).toBe("H2");

    // modified: H3
    const h3 = mount(Headline, {
      propsData: {
        tag: "h3"
      }
    });

    expect(h3.element.tagName).toBe("H3");

  });

  it("renders nested link", () => {

    // default: h2
    const wrapper = mount(Headline, {
      propsData: {
        link: "/test"
      }
    });

    expect(wrapper.contains(Link)).toBe(true);

  });

  it("has size attribute", () => {

    // default: h2
    const wrapper = mount(Headline, {
      propsData: {
        size: "large"
      }
    });

    expect(wrapper.attributes("data-size")).toBe("large");

  });

  it("has theme attribute", () => {

    // default: h2
    const wrapper = mount(Headline, {
      propsData: {
        theme: "negative"
      }
    });

    expect(wrapper.attributes("data-theme")).toBe("negative");

  });

});
