import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Text from "./Text.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Text, { props, attrs }).find(".k-text");
}

describe("Text.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-text");
	});

	// props
	describe("align prop", () => {
		it("renders value as attribute", () => {
			const wrapper = mount({ align: "center" });
			expect(wrapper.attributes("data-align")).toBe("center");
		});
	});

	describe("size prop", () => {
		it("renders value as attribute", () => {
			const wrapper = mount({ size: "large" });
			expect(wrapper.attributes("data-size")).toBe("large");
		});
	});

	describe("html prop", () => {
		it("renders html content", () => {
			const wrapper = mount({ html: "<b>Hello</b>" });
			expect(wrapper.html()).toContain("<b>Hello</b>");
		});

		it("does not render slot when html is provided", () => {
			const wrapper = vueMount(Text, {
				props: { html: "<b>Hello</b>" },
				slots: { default: "Slot content" }
			});
			expect(wrapper.text()).not.toContain("Slot content");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = vueMount(Text, {
				slots: { default: "Hello" }
			});
			expect(wrapper.html()).toContain("Hello");
		});
	});
});
