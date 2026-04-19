import { describe, it, expect } from "@test/unit";
import { mount } from "@vue/test-utils";
import IconFrame from "./IconFrame.vue";

describe("IconFrame.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(IconFrame, "K-FRAME", "k-icon-frame");
		it.acceptsClass(IconFrame);
		it.acceptsStyle(IconFrame);
		it.inheritsNoAttrs(IconFrame);

		it("passes props to k-frame", () => {
			const wrapper = mount(IconFrame, {
				props: { ratio: "16/9", theme: "positive" }
			});
			expect(wrapper.attributes("element")).toBe("figure");
			expect(wrapper.attributes("ratio")).toBe("16/9");
			expect(wrapper.attributes("theme")).toBe("positive");
		});
	});

	// props
	describe("alt prop", () => {
		it("passes to k-icon", () => {
			const wrapper = mount(IconFrame, {
				props: { icon: "edit", alt: "Edit item" }
			});
			expect(wrapper.find("k-icon").attributes("alt")).toBe("Edit item");
		});
	});

	describe("color prop", () => {
		it("passes to k-icon", () => {
			const wrapper = mount(IconFrame, {
				props: { icon: "edit", color: "red" }
			});
			expect(wrapper.find("k-icon").attributes("color")).toBe("red");
		});
	});

	describe("icon prop", () => {
		it("passes as type to k-icon", () => {
			const wrapper = mount(IconFrame, { props: { icon: "edit" } });
			expect(wrapper.find("k-icon").attributes("type")).toBe("edit");
		});
	});
});
