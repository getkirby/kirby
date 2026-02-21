// @vitest-environment jsdom
import { mount } from "@vue/test-utils";
import { describe, it, expect } from "vitest";
import Stack from "./Stack.vue";

describe("Stack.vue", () => {
	it("renders a <div> with class k-stack", () => {
		const wrapper = mount(Stack);
		expect(wrapper.element.tagName).toBe("DIV");
		expect(wrapper.classes()).toContain("k-stack");
	});

	describe("align prop", () => {
		it("is not set by default", () => {
			const wrapper = mount(Stack);
			expect(wrapper.element.style.alignItems).toBe("");
		});

		it("sets align-items inline style", () => {
			const wrapper = mount(Stack, { props: { align: "center" } });
			expect(wrapper.element.style.alignItems).toBe("center");
		});
	});

	describe("direction prop", () => {
		it("is not set by default", () => {
			const wrapper = mount(Stack);
			expect(wrapper.element.style.flexDirection).toBe("");
		});

		it("sets flex-direction inline style", () => {
			const wrapper = mount(Stack, { props: { direction: "row" } });
			expect(wrapper.element.style.flexDirection).toBe("row");
		});
	});

	describe("gap prop", () => {
		it("is not set by default", () => {
			const wrapper = mount(Stack);
			expect(wrapper.element.style.gap).toBe("");
		});

		it("sets gap inline style", () => {
			const wrapper = mount(Stack, { props: { gap: "1rem" } });
			expect(wrapper.element.style.gap).toBe("1rem");
		});
	});

	describe("justify prop", () => {
		it("is not set by default", () => {
			const wrapper = mount(Stack);
			expect(wrapper.element.style.justifyContent).toBe("");
		});

		it("sets justify-content inline style", () => {
			const wrapper = mount(Stack, { props: { justify: "space-between" } });
			expect(wrapper.element.style.justifyContent).toBe("space-between");
		});
	});

	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount(Stack, {
				slots: { default: "<p>Hello</p>" }
			});
			expect(wrapper.find("p").text()).toBe("Hello");
		});
	});
});
