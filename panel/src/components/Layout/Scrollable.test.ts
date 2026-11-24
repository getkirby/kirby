import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Scrollable from "./Scrollable.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Scrollable, {
		props,
		attrs,
		slots: { default: "<p>Long content</p>" }
	});
}

describe("k-scrollable", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-scrollable");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("axis prop", () => {
		it("scrolls vertically by default", () => {
			expect(mount().attributes("data-axis")).toBe("block");
		});

		it("scrolls horizontally", () => {
			expect(mount({ axis: "inline" }).attributes("data-axis")).toBe("inline");
		});
	});

	describe("element prop", () => {
		it("renders the element", () => {
			expect(mount({ element: "ul" }).element.tagName).toBe("UL");
		});
	});

	describe("fade prop", () => {
		it("fades both edges by default", () => {
			expect(mount().attributes("data-fade")).toBe("true");
		});

		it("fades one edge", () => {
			expect(mount({ fade: "end" }).attributes("data-fade")).toBe("end");
		});

		it("doesn't fade", () => {
			expect(mount({ fade: false }).attributes("data-fade")).toBe("false");
		});

		it("only accepts booleans, start and end", () => {
			const validator = (
				Scrollable.props.fade as { validator: (v: unknown) => boolean }
			).validator;
			expect(validator(true)).toBe(true);
			expect(validator("start")).toBe(true);
			expect(validator("middle")).toBe(false);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the content", () => {
			expect(mount().find("p").text()).toBe("Long content");
		});
	});
});
