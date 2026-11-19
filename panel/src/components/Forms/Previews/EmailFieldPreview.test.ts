import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import EmailFieldPreview from "./EmailFieldPreview.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(EmailFieldPreview, {
		props: { value: "mail@getkirby.com", ...props },
		attrs
	});
}

describe("k-email-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "P", "k-email-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("keeps the class of the URL preview", () => {
			expect(mount().classes()).toContain("k-url-field-preview");
		});
	});

	// props
	describe("value prop", () => {
		it("links to the email address", () => {
			const wrapper = mount();
			expect(wrapper.find("k-link").attributes("to")).toBe("mail@getkirby.com");
			expect(wrapper.find("k-link span").text()).toBe("mail@getkirby.com");
		});
	});
});
