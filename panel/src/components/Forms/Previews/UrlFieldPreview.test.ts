import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import UrlFieldPreview from "./UrlFieldPreview.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(UrlFieldPreview, {
		props: { value: "https://getkirby.com", ...props },
		attrs
	});
}

describe("k-url-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "P", "k-url-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("wraps the link with the column's before and after", () => {
			const wrapper = mount({ column: { after: ")", before: "(" } });
			expect(wrapper.text()).toBe("( https://getkirby.com )");
		});
	});

	// props
	describe("value prop", () => {
		it("links to the URL", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-link")).toBe("true");
			expect(wrapper.find("k-link").attributes("to")).toBe(
				"https://getkirby.com"
			);
			expect(wrapper.find("k-link span").text()).toBe("https://getkirby.com");
		});

		it("links to the href of an object with its text", () => {
			const wrapper = mount({
				value: { href: "https://getkirby.com", text: "Kirby" }
			});
			expect(wrapper.find("k-link").attributes("to")).toBe(
				"https://getkirby.com"
			);
			expect(wrapper.find("k-link span").text()).toBe("Kirby");
		});

		it("marks a missing link", () => {
			const wrapper = mount({ value: "" });
			expect(wrapper.attributes("data-link")).toBe("false");
		});
	});

	// events
	describe("events", () => {
		it("keeps clicks on the link from the row", async () => {
			const wrapper = mount();
			const click = vi.fn();
			wrapper.element.addEventListener("click", click);
			await wrapper.find("k-link").trigger("click");
			expect(click).not.toHaveBeenCalled();
		});
	});
});
