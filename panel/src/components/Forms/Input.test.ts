import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import preserveListeners from "@/mixins/preserveListeners";
import Input from "./Input.vue";
import StringInput from "./Input/StringInput.vue";
import TextInput from "./Input/TextInput.vue";

function mount(attrs: Record<string, unknown> = {}) {
	return vueMount(Input, {
		props: { type: "text" },
		attrs,
		global: {
			// registered globally in the Panel
			components: {
				"k-string-input": StringInput,
				"k-text-input": TextInput
			},
			directives: { direction: {} },
			mixins: [preserveListeners]
		}
	});
}

describe("Input.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// events
	describe.each([
		["blur", "onBlur"],
		["focus", "onFocus"],
		["keydown", "onKeydown"]
	])("%s event", (event, listener) => {
		it("calls the listener once", async () => {
			const handler = vi.fn();
			const wrapper = mount({ [listener]: handler });
			await wrapper.find("input").trigger(event);
			expect(handler).toHaveBeenCalledOnce();
		});
	});
});
