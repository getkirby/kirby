import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { HtmlString } from "@/panel/html";
import TagFieldPreview from "./TagFieldPreview.vue";

const Tag = {
	name: "k-tag",
	props: ["text", "theme"],
	template: "<span />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(TagFieldPreview, {
		props: { value: "Design", ...props },
		attrs,
		global: {
			stubs: { "k-tag": Tag }
		}
	});
}

describe("k-tag-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "UL", "k-tag-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("looks like the tags preview", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-tags-field-preview");
			expect(wrapper.classes()).toContain("k-tags");
		});

		it("renders the value as a light tag", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Tag).props()).toStrictEqual({
				text: "Design",
				theme: "light"
			});
		});
	});

	// props
	describe("html prop", () => {
		it("marks the value as trusted HTML", () => {
			const wrapper = mount({ html: true, value: "<b>Design</b>" });
			const text = wrapper.findComponent(Tag).props("text");
			expect(text).toBeInstanceOf(HtmlString);
			expect(String(text)).toBe("<b>Design</b>");
		});

		it("is deprecated", () => {
			const deprecated = vi.mocked(window.panel.deprecated);
			deprecated.mockClear();
			mount({ html: true });
			expect(deprecated).toHaveBeenCalledOnce();
		});

		it("keeps the value as plain text by default", () => {
			const deprecated = vi.mocked(window.panel.deprecated);
			deprecated.mockClear();
			const wrapper = mount({ value: "<b>Design</b>" });
			expect(wrapper.findComponent(Tag).props("text")).toBe("<b>Design</b>");
			expect(deprecated).not.toHaveBeenCalled();
		});
	});

	// events
	describe("events", () => {
		it("keeps clicks on the tag from the row", async () => {
			const wrapper = mount();
			const click = vi.fn();
			wrapper.element.addEventListener("click", click);
			await wrapper.findComponent(Tag).trigger("click");
			expect(click).not.toHaveBeenCalled();
		});
	});
});
