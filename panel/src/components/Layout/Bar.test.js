// @vitest-environment jsdom
import { mount } from "@vue/test-utils";
import { describe, it, expect } from "vitest";
import Bar from "./Bar.vue";

describe("Bar.vue", () => {
	it("renders a <div> with class k-bar", () => {
		const wrapper = mount(Bar);
		expect(wrapper.element.tagName).toBe("DIV");
		expect(wrapper.classes()).toContain("k-bar");
	});

	describe("align prop", () => {
		it("defaults to start", () => {
			const wrapper = mount(Bar);
			expect(wrapper.attributes("data-align")).toBe("start");
		});

		it("reflects the prop as data-align attribute", () => {
			const wrapper = mount(Bar, { props: { align: "center" } });
			expect(wrapper.attributes("data-align")).toBe("center");
		});
	});

	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount(Bar, {
				slots: { default: "<button>Save</button>" }
			});
			expect(wrapper.find("button").text()).toBe("Save");
		});
	});
});
