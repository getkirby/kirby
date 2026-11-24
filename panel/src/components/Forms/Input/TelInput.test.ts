import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TelInput from "./TelInput.vue";
import StringInput from "./StringInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(TelInput, {
		props,
		attrs,
		// focus and selection only work on connected elements
		attachTo: document.body,
		global: {
			// registered globally in the Panel
			components: { "k-string-input": StringInput },
			directives: { direction: {} }
		}
	});
}

describe("k-tel-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-tel-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a k-string-input", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-string-input");
		});
	});

	// props
	describe("autocomplete prop", () => {
		it("defaults to tel", () => {
			const wrapper = mount();
			expect(wrapper.attributes("autocomplete")).toBe("tel");
		});

		it("sets the attribute", () => {
			const wrapper = mount({ autocomplete: "off" });
			expect(wrapper.attributes("autocomplete")).toBe("off");
		});
	});

	describe("placeholder prop", () => {
		it("defaults to the translated placeholder", () => {
			const wrapper = mount();
			expect(wrapper.attributes("placeholder")).toBe("tel.placeholder");
		});

		it("sets the attribute", () => {
			const wrapper = mount({ placeholder: "+49 …" });
			expect(wrapper.attributes("placeholder")).toBe("+49 …");
		});
	});

	describe("type prop", () => {
		it("is always tel", () => {
			const wrapper = mount({ type: "text" });
			expect(wrapper.attributes("type")).toBe("tel");
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-string-input", () => {
			const wrapper = mount({ value: "+49 30 1234567" });
			expect(wrapper.element.value).toBe("+49 30 1234567");
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
			const wrapper = mount({ value: "+49 30 1234567" });
			wrapper.vm.select();
			expect(wrapper.element.selectionStart).toBe(0);
			expect(wrapper.element.selectionEnd).toBe(14);
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-string-input", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("+49 30 1234567");
			expect(wrapper.emitted("input")).toStrictEqual([["+49 30 1234567"]]);
		});
	});
});
