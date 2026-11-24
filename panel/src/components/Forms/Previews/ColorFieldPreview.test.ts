import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ColorFieldPreview from "./ColorFieldPreview.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(ColorFieldPreview, {
		props: { value: "#ff0000", ...props },
		attrs
	});
}

const options = [
	{ text: "Red", value: "#ff0000" },
	{ text: "Blue", value: "#0000ff" }
];

describe("k-color-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-color-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("shows the color", () => {
			const wrapper = mount();
			expect(wrapper.find("k-color-frame").attributes("color")).toBe("#ff0000");
		});
	});

	// props
	describe("field prop", () => {
		it("names the color by its option", () => {
			const wrapper = mount({ field: { options } });
			expect(wrapper.text()).toBe("Red");
		});

		it("matches an option in another notation", () => {
			const wrapper = mount({
				field: { format: "hex", options },
				value: "rgb(0, 0, 255)"
			});
			expect(wrapper.text()).toBe("Blue");
		});

		it("names no color without matching option", () => {
			const wrapper = mount({ field: { options }, value: "#00ff00" });
			expect(wrapper.text()).toBe("");
		});
	});

	describe("value prop", () => {
		it("names no color without options", () => {
			expect(mount().text()).toBe("");
		});
	});
});
