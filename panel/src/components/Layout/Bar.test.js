import { mount } from "@vue/test-utils";
import { describe, it, expect } from "vitest";
import Bar from "./Bar.vue";

describe("Bar.vue", () => {
	// $el
	describe("element", () => {
		it("renders a <div> with class k-bar", () => {
			const wrapper = mount(Bar);
			expect(wrapper.element.tagName).toBe("DIV");
			expect(wrapper.classes()).toContain("k-bar");
		});

		it("accepts a custom class", () => {
			const wrapper = mount(Bar, { attrs: { class: "my-class" } });
			expect(wrapper.classes()).toContain("my-class");
		});

		it("accepts a custom style", () => {
			const wrapper = mount(Bar, { attrs: { style: "--foo: 1" } });
			expect(wrapper.attributes("style")).toContain("--foo");
		});
	});

	// props
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

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount(Bar, {
				slots: { default: "<button>Foo</button>" }
			});
			expect(wrapper.find("button").text()).toBe("Foo");
		});
	});
});
