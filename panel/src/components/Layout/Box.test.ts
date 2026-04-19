import { describe, it, expect } from "@test/unit";
import { mount } from "@vue/test-utils";
import Box from "./Box.vue";

describe("Box.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(Box, "DIV", "k-box");
		it.acceptsClass(Box);
		it.acceptsStyle(Box);
	});

	// props
	describe("align prop", () => {
		it("defaults to start", () => {
			const wrapper = mount(Box);
			expect(wrapper.attributes("data-align")).toBe("start");
		});

		it("renders value as attribute", () => {
			const wrapper = mount(Box, { props: { align: "center" } });
			expect(wrapper.attributes("data-align")).toBe("center");
		});
	});

	describe("button prop", () => {
		it("renders as a button element", () => {
			const wrapper = mount(Box, { props: { button: true } });
			expect(wrapper.element.tagName).toBe("BUTTON");
		});

		it("sets type attribute to button", () => {
			const wrapper = mount(Box, { props: { button: true } });
			expect(wrapper.attributes("type")).toBe("button");
		});
	});

	describe("theme prop", () => {
		it("renders value as attribute", () => {
			const wrapper = mount(Box, { props: { theme: "positive" } });
			expect(wrapper.attributes("data-theme")).toBe("positive");
		});
	});

	describe("height prop", () => {
		it("sets the CSS custom property", () => {
			const wrapper = mount(Box, { props: { height: "10rem" } });
			expect(wrapper.attributes("style")).toContain("--box-height: 10rem");
		});
	});

	describe("icon prop", () => {
		it("renders a k-icon with the correct type", () => {
			const wrapper = mount(Box, { props: { icon: "add" } });
			expect(wrapper.find("k-icon").attributes("type")).toBe("add");
		});

		it("does not render a k-icon when not provided", () => {
			const wrapper = mount(Box);
			expect(wrapper.find("k-icon").exists()).toBe(false);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount(Box, {
				slots: { default: "<p>Hello</p>" }
			});
			expect(wrapper.find("p").text()).toBe("Hello");
		});
	});
});
