import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Text from "@/components/Text/Text.vue";
import WriterFieldPreview from "./WriterFieldPreview.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(WriterFieldPreview, {
		props,
		attrs,
		global: {
			components: { "k-text": Text }
		}
	});
}

describe("WriterFieldPreview.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-writer-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// computed
	describe("html computed", () => {
		it("renders the sanitized value as HTML", async () => {
			const wrapper = mount({
				value: "<b>Hello</b><img src=x onerror=alert(1)>"
			});

			await vi.waitFor(() => {
				expect(wrapper.find(".k-text").html()).toContain(
					"<strong>Hello</strong>"
				);
			});

			expect(wrapper.find(".k-text").html()).not.toContain("img");
		});
	});
});
