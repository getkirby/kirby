import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import PasswordInput from "./PasswordInput.vue";
import StringInput from "./StringInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(PasswordInput, {
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

describe("k-password-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-password-input");
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
		it("defaults to new-password", () => {
			const wrapper = mount();
			expect(wrapper.attributes("autocomplete")).toBe("new-password");
		});

		it("sets the attribute", () => {
			const wrapper = mount({ autocomplete: "off" });
			expect(wrapper.attributes("autocomplete")).toBe("off");
		});
	});

	describe("type prop", () => {
		it("is always password", () => {
			const wrapper = mount({ type: "text" });
			expect(wrapper.attributes("type")).toBe("password");
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-string-input", () => {
			const wrapper = mount({ value: "topsecret" });
			expect(wrapper.element.value).toBe("topsecret");
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
			const wrapper = mount({ value: "topsecret" });
			wrapper.vm.select();
			expect(wrapper.element.selectionStart).toBe(0);
			expect(wrapper.element.selectionEnd).toBe(9);
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-string-input", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("topsecret");
			expect(wrapper.emitted("input")).toStrictEqual([["topsecret"]]);
		});
	});
});
