import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import StringInput from "./StringInput.vue";
import TextInput from "./TextInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(TextInput, {
		props,
		attrs,
		// focus and selection only work on connected elements
		attachTo: document.body,
		global: {
			// registered globally in the Panel
			components: { "k-string-input": StringInput }
		}
	});
}

describe("k-text-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-text-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a k-string-input", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-string-input");
		});
	});

	// props
	describe("type prop", () => {
		it("is always text", () => {
			const wrapper = mount({ type: "email" });
			expect(wrapper.attributes("type")).toBe("text");
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-string-input", () => {
			const wrapper = mount({ value: "abc" });
			expect(wrapper.element.value).toBe("abc");
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(wrapper.element);
		});
	});

	describe("select()", () => {
		it("selects the value", () => {
			const wrapper = mount({ value: "abc" });
			wrapper.vm.select();
			expect(wrapper.element.selectionStart).toBe(0);
			expect(wrapper.element.selectionEnd).toBe(3);
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-string-input", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("abc");
			expect(wrapper.emitted("input")).toStrictEqual([["abc"]]);
		});
	});
});
