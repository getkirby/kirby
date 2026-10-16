import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import html from "@/panel/html";
import Text from "@/components/Text/Text.vue";
import HtmlFieldPreview from "./HtmlFieldPreview.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(HtmlFieldPreview, {
		props,
		attrs,
		global: {
			components: { "k-text": Text }
		}
	});
}

describe("HtmlFieldPreview.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-html-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("value prop", () => {
		it("escapes a plain string", () => {
			const wrapper = mount({ value: "<b>Hello</b>" });
			expect(wrapper.find(".k-text").html()).toContain(
				"&lt;b&gt;Hello&lt;/b&gt;"
			);
		});

		it("renders trusted HTML as-is", () => {
			const wrapper = mount({ value: html("<b>Hello</b>") });
			expect(wrapper.find(".k-text").html()).toContain("<b>Hello</b>");
		});
	});
});
