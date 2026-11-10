import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import * as inputs from "@/components/Forms/Input";
import Editor from "@/components/Forms/Writer/Editor";
import Text from "./Text.vue";

function mount(attrs = {}) {
	return vueMount(Text, {
		attrs,
		global: {
			// the input as registered by the Panel
			// https://github.com/getkirby/kirby/issues/8762
			components: { "k-writer-input": inputs["k-writer-input"] },
			directives: { direction: {} },
			mocks: { $panel: { events: { off: vi.fn(), on: vi.fn() } } }
		}
	});
}

describe("Text.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-type-text-input");
		it.inheritsNoAttrs(mount);
	});

	// methods
	describe("focus()", () => {
		it("focuses the writer right after rendering", () => {
			const focus = vi.spyOn(Editor.prototype, "focus");

			const wrapper = mount();
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();

			focus.mockRestore();
		});
	});
});
