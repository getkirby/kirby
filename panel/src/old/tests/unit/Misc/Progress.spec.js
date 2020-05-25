import { mount } from "@vue/test-utils";
import Progress from "@/components/Misc/Progress.vue";

describe("Progress.vue", () => {

  it("renders", () => {
    const wrapper = mount(Progress);
    expect(wrapper.is("progress")).toBe(true);
    expect(wrapper.is(".k-progress")).toBe(true);
    expect(wrapper.attributes("max")).toBe("100");
    expect(wrapper.attributes("value")).toBe(undefined);
    expect(wrapper.text()).toBe("0%");
  });

});
