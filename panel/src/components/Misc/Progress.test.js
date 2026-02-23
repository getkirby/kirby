import { mount } from "@vue/test-utils";
import { describe, it, expect } from "vitest";
import Progress from "./Progress.vue";

describe("Progress.vue", () => {
	// $el
	describe("element", () => {
		it("renders a <progress> with class k-progress", () => {
			const wrapper = mount(Progress);
			expect(wrapper.element.tagName).toBe("PROGRESS");
			expect(wrapper.classes()).toContain("k-progress");
		});

		it("accepts a custom class", () => {
			const wrapper = mount(Progress, { attrs: { class: "my-class" } });
			expect(wrapper.classes()).toContain("my-class");
		});

		it("accepts a custom style", () => {
			const wrapper = mount(Progress, { attrs: { style: "--foo: 1" } });
			expect(wrapper.attributes("style")).toContain("--foo");
		});

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
