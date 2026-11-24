import { describe, it, expect, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import preserveListeners from "@/mixins/preserveListeners";
import Fields from "./Fields.vue";

function mount(attrs = {}) {
	return vueMount(Fields, {
		attrs,
		global: {
			mixins: [preserveListeners]
		},
		props: {
			tabs: {}
		}
	});
}

describe("k-block-type-fields", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV");
		it.inheritsNoAttrs(mount);
	});

	// events
	describe("copy event", () => {
		it("is not emitted for a native copy event in the form", () => {
			const onCopy = vi.fn();
			const wrapper = mount({ onCopy });

			wrapper
				.find(".k-block-type-fields-form")
				.element.dispatchEvent(new Event("copy", { bubbles: true }));

			expect(onCopy).not.toHaveBeenCalled();
		});
	});

	describe("paste event", () => {
		it("is not emitted for a native paste event in the form", () => {
			const onPaste = vi.fn();
			const wrapper = mount({ onPaste });

			wrapper
				.find(".k-block-type-fields-form")
				.element.dispatchEvent(new Event("paste", { bubbles: true }));

			expect(onPaste).not.toHaveBeenCalled();
		});
	});
});
