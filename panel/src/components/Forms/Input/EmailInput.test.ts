import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import EmailInput from "./EmailInput.vue";
import StringInput from "./StringInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(EmailInput, {
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

describe("k-email-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-email-input");
		it.inheritsNoAttrs(mount);

		it("renders a k-string-input", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-string-input");
		});
	});

	// props
	describe("autocomplete prop", () => {
		it("defaults to email", () => {
			const wrapper = mount();
			expect(wrapper.attributes("autocomplete")).toBe("email");
		});

		it("sets the attribute", () => {
			const wrapper = mount({ autocomplete: "off" });
			expect(wrapper.attributes("autocomplete")).toBe("off");
		});
	});

	describe("placeholder prop", () => {
		it("defaults to the translated placeholder", () => {
			const wrapper = mount();
			expect(wrapper.attributes("placeholder")).toBe("email.placeholder");
		});

		it("sets the attribute", () => {
			const wrapper = mount({ placeholder: "mail@example.com" });
			expect(wrapper.attributes("placeholder")).toBe("mail@example.com");
		});
	});

	describe("type prop", () => {
		it("is always email", () => {
			const wrapper = mount({ type: "text" });
			expect(wrapper.attributes("type")).toBe("email");
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-string-input", () => {
			const wrapper = mount({ value: "mail@example.com" });
			expect(wrapper.element.value).toBe("mail@example.com");
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
		// email inputs expose no selection range to read back
		it("selects the value", () => {
			const wrapper = mount({ value: "a@b.de" });
			const select = vi.spyOn(wrapper.element, "select");
			wrapper.vm.select();
			expect(select).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-string-input", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("mail@example.com");
			expect(wrapper.emitted("input")).toStrictEqual([["mail@example.com"]]);
		});
	});
});
