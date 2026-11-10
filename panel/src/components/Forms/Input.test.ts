import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import preserveListeners from "@/mixins/preserveListeners";
import Input from "./Input.vue";
import StringInput from "./Input/StringInput.vue";
import TextInput from "./Input/TextInput.vue";

// stands in for the input types that emit `submit`
const SubmitInput = {
	emits: ["input", "submit"],
	template: "<input />"
};

function mount(
	attrs: Record<string, unknown> = {},
	{ props = {}, slots = {} } = {}
) {
	return vueMount(Input, {
		props: { type: "text", ...props },
		attrs,
		slots,
		// focus only works on connected elements
		attachTo: document.body,
		global: {
			// registered globally in the Panel
			components: {
				"k-string-input": StringInput,
				"k-submit-input": SubmitInput,
				"k-text-input": TextInput
			},
			directives: { direction: {} },
			mixins: [preserveListeners]
		}
	});
}

describe("k-input", () => {
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

	describe("click event", () => {
		it("focuses the input on a click next to it", async () => {
			const wrapper = mount();
			await wrapper.find(".k-input-element").trigger("click");
			expect(document.activeElement).toBe(wrapper.find("input").element);
		});

		it("focuses the first input of the default slot", async () => {
			const wrapper = mount({}, { slots: { default: "<textarea />" } });
			await wrapper.find(".k-input-element").trigger("click");
			expect(document.activeElement).toBe(wrapper.find("textarea").element);
		});

		it("keeps the focus on the clicked input", async () => {
			const wrapper = mount(
				{},
				{ slots: { default: '<input class="a" /><input class="b" />' } }
			);
			await wrapper.find(".b").trigger("click");
			expect(document.activeElement).toBe(wrapper.find(".b").element);
		});

		it("stops the click from bubbling up", async () => {
			const handler = vi.fn();
			const wrapper = mount();
			wrapper.element.addEventListener("click", handler);
			await wrapper.find(".k-input-element").trigger("click");
			expect(handler).not.toHaveBeenCalled();
		});
	});

	describe("input event", () => {
		it("emits the value of the inner input", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("foo");
			expect(wrapper.emitted("input")).toStrictEqual([["foo"]]);
		});
	});

	describe("submit event", () => {
		it("emits the submit of the inner input", () => {
			const wrapper = mount({}, { props: { type: "submit" } });
			wrapper.findComponent(SubmitInput).vm.$emit("submit", "foo");
			expect(wrapper.emitted("submit")).toStrictEqual([["foo"]]);
		});
	});
});
