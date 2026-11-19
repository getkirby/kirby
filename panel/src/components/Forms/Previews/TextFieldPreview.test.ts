import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TextFieldPreview from "./TextFieldPreview.vue";

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(TextFieldPreview, {
		props: { value: "Hello", ...props },
		attrs,
		slots
	});
}

describe("k-text-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "P", "k-text-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("column prop", () => {
		it("wraps the text with the column's before and after", () => {
			const wrapper = mount({
				column: { after: "EUR", before: "~" },
				value: "12"
			});
			expect(wrapper.text()).toBe("~ 12 EUR");
		});
	});

	describe("value prop", () => {
		it("shows the value", () => {
			expect(mount().text()).toBe("Hello");
		});

		it("shows a number", () => {
			expect(mount({ value: 12 }).text()).toBe("12");
		});

		it("escapes HTML", () => {
			const wrapper = mount({ value: "<b>Hello</b>" });
			expect(wrapper.find("b").exists()).toBe(false);
			expect(wrapper.text()).toBe("<b>Hello</b>");
		});
	});

	// slots
	describe("default slot", () => {
		it("replaces the text", () => {
			const wrapper = mount(
				{ column: { after: "!" } },
				{},
				{ default: "<strong>Hi</strong>" }
			);
			expect(wrapper.html()).toContain("<strong>Hi</strong>");
			expect(wrapper.text()).toBe("Hi !");
		});
	});
});
