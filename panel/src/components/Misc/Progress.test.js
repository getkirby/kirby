// @vitest-environment jsdom
import { mount } from "@vue/test-utils";
import { describe, it, expect } from "vitest";
import Progress from "./Progress.vue";

describe("Progress.vue", () => {
	it("renders a <progress> element", () => {
		const wrapper = mount(Progress);
		expect(wrapper.element.tagName).toBe("PROGRESS");
	});

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

	describe("max attribute", () => {
		it("is always 100", () => {
			const wrapper = mount(Progress);
			expect(wrapper.attributes("max")).toBe("100");
		});
	});
});
