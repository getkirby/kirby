import { describe, it, expect, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import preserveListeners from "@/mixins/preserveListeners";
import Fields from "./Fields.vue";

function mount(attrs: Record<string, unknown> = {}) {
	return vueMount(Fields, {
		attrs,
		global: {
			mixins: [preserveListeners]
		},
		props: {
			content: {},
			endpoints: { field: "pages/test/fields/blocks" },
			fieldset: {
				name: "Custom",
				tabs: { content: { fields: { caption: { type: "text" } } } },
				wysiwyg: true
			},
			id: "test",
			tabs: { content: { fields: { caption: { type: "text" } } } }
		}
	});
}

describe("Fields.vue", () => {
	describe("native clipboard events", () => {
		it("do not trigger the block's copy action", () => {
			const onCopy = vi.fn();
			const wrapper = mount({ onCopy });

			wrapper
				.find(".k-block-type-fields-form")
				.element.dispatchEvent(new Event("copy", { bubbles: true }));

			expect(onCopy).not.toHaveBeenCalled();
		});

		it("do not trigger the block's paste action", () => {
			const onPaste = vi.fn();
			const wrapper = mount({ onPaste });

			wrapper
				.find(".k-block-type-fields-form")
				.element.dispatchEvent(new Event("paste", { bubbles: true }));

			expect(onPaste).not.toHaveBeenCalled();
		});
	});
});
