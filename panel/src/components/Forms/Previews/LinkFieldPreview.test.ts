import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import LinkFieldPreview from "./LinkFieldPreview.vue";

function preview(name: string) {
	return {
		name,
		props: ["removable", "value"],
		emits: ["remove"],
		template: "<ul />"
	};
}

const FilesPreview = preview("k-files-field-preview");
const PagesPreview = preview("k-pages-field-preview");

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(LinkFieldPreview, {
		props: { value: "https://getkirby.com", ...props },
		attrs,
		slots,
		global: {
			components: {
				"k-files-field-preview": FilesPreview,
				"k-pages-field-preview": PagesPreview
			}
		}
	});
}

describe("k-link-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-link-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	describe("element of a page", () => {
		const page = (attrs = {}) => mount({ value: "page://a" }, attrs);
		it.inheritsNoAttrs(page);
	});

	// props
	describe("removable prop", () => {
		it("makes a page removable", () => {
			const wrapper = mount({ removable: true, value: "page://a" });
			expect(wrapper.findComponent(PagesPreview).props("removable")).toBe(true);
		});
	});

	describe("type prop", () => {
		it("replaces the detected type", () => {
			const wrapper = mount({ type: "custom" });
			expect(wrapper.find("a").exists()).toBe(false);
			expect(wrapper.text()).toBe("https://getkirby.com");
		});

		it("shows the placeholder for a page without value", () => {
			const wrapper = mount(
				{ type: "page", value: "" },
				{},
				{ placeholder: "<button>Select page</button>" }
			);
			expect(wrapper.html()).toBe("<button>Select page</button>");
		});
	});

	describe("value prop", () => {
		it("links to a URL in a new tab", () => {
			const wrapper = mount();
			const link = wrapper.find("a");
			expect(wrapper.classes()).toContain("k-url-field-preview");
			expect(link.attributes("href")).toBe("https://getkirby.com");
			expect(link.attributes("target")).toBe("_blank");
			expect(link.text()).toBe("https://getkirby.com");
		});

		it("links to an email address", () => {
			const wrapper = mount({ value: "mailto:mail@getkirby.com" });
			const link = wrapper.find("a");
			expect(link.attributes("href")).toBe("mailto:mail@getkirby.com");
			expect(link.text()).toBe("mail@getkirby.com");
		});

		it("links to a phone number", () => {
			const wrapper = mount({ value: "tel:+49123456" });
			const link = wrapper.find("a");
			expect(link.attributes("href")).toBe("tel:+49123456");
			expect(link.text()).toBe("+49123456");
		});

		it("shows an anchor as text", () => {
			const wrapper = mount({ value: "#top" });
			expect(wrapper.classes()).not.toContain("k-url-field-preview");
			expect(wrapper.find("a").exists()).toBe(false);
			expect(wrapper.text()).toBe("#top");
		});

		it("previews a page", () => {
			const wrapper = mount({ value: "page://a" });
			expect(wrapper.findComponent(PagesPreview).props()).toStrictEqual({
				removable: false,
				value: ["page://a"]
			});
		});

		it("previews a file", () => {
			const wrapper = mount({ value: "file://a" });
			expect(wrapper.findComponent(FilesPreview).props()).toStrictEqual({
				removable: false,
				value: ["file://a"]
			});
		});
	});

	// events
	describe("events", () => {
		it("emits remove from the model preview", () => {
			const wrapper = mount({ removable: true, value: "file://a" });
			wrapper.findComponent(FilesPreview).vm.$emit("remove", 0);
			expect(wrapper.emitted("remove")).toStrictEqual([[0]]);
		});

		it("keeps clicks on the link from the row", async () => {
			const wrapper = mount();
			const click = vi.fn();
			wrapper.element.addEventListener("click", click);
			await wrapper.find("a").trigger("click");
			expect(click).not.toHaveBeenCalled();
		});
	});
});
