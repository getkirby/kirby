import { describe, it, expect } from "@test/unit";
import { mount } from "@vue/test-utils";
import Progress from "./Progress.vue";

describe("Progress.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(Progress, "PROGRESS", "k-progress");
		it.acceptsClass(Progress);
		it.acceptsStyle(Progress);

		it("max attribute is always 100", () => {
			const wrapper = mount(Progress);
			expect(wrapper.attributes("max")).toBe("100");
		});
	});

	// props
	describe("value prop", () => {
		it("defaults to 0", () => {
			const wrapper = mount(Progress);
			expect(wrapper.attributes("value")).toBe("0");
		});

		it("reflects the prop as attribute", () => {
			const wrapper = mount(Progress, { props: { value: 42 } });
			expect(wrapper.attributes("value")).toBe("42");
		});

		it("renders value as percentage text", () => {
			const wrapper = mount(Progress, { props: { value: 75 } });
			expect(wrapper.text()).toBe("75%");
		});
	});
});
