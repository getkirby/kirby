import { describe, it, expect } from "@test/unit";
import { mount } from "@vue/test-utils";
import ColorFrame from "./ColorFrame.vue";

describe("ColorFrame.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(ColorFrame, "K-FRAME", "k-color-frame");
		it.acceptsClass(ColorFrame);
		it.acceptsStyle(ColorFrame);
		it.inheritsNoAttrs(ColorFrame);

		it("passes props to k-frame", () => {
			const wrapper = mount(ColorFrame, {
				props: { ratio: "16/9", theme: "positive" }
			});
			expect(wrapper.attributes("ratio")).toBe("16/9");
			expect(wrapper.attributes("theme")).toBe("positive");
		});
	});

	// props
	describe("color prop", () => {
		it("sets the --color-frame-back CSS custom property", () => {
			const wrapper = mount(ColorFrame, { props: { color: "#efefef" } });
			expect(wrapper.attributes("style")).toContain(
				"--color-frame-back: #efefef"
			);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content as direct child of k-frame", () => {
			const slot = "<span>preview</span>";
			const wrapper = mount(ColorFrame, {
				slots: { default: slot }
			});
			expect(wrapper.element.innerHTML).toBe(slot);
		});
	});
});
