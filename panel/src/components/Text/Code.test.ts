import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { defineComponent } from "vue";
import Code from "./Code.vue";

// k-highlight renders the slot's single <div>, which becomes the root
// (as a fragment, VTU's wrapper.element would be its mount container)
const global = {
	stubs: {
		"k-highlight": defineComponent({
			render() {
				return this.$slots.default?.()[0];
			}
		})
	}
};

function mount(props = {}) {
	return vueMount(Code, {
		props,
		global,
		slots: { default: "const x = 1;" }
	});
}

describe("k-code", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV");

		it("wraps a pre.k-code", () => {
			const wrapper = mount();
			const pre = wrapper.find("pre.k-code");
			expect(pre.element.parentElement).toBe(wrapper.element);
		});
	});

	// props
	describe("language prop", () => {
		it("sets data-language attribute on pre", () => {
			const wrapper = mount({ language: "html" });
			expect(wrapper.find("pre").attributes("data-language")).toBe("html");
		});

		it("sets language class on code element", () => {
			const wrapper = mount({ language: "html" });
			expect(wrapper.find("code").classes()).toContain("language-html");
		});

		it("omits data-language when not provided", () => {
			const wrapper = mount();
			expect(wrapper.find("pre").attributes("data-language")).toBeUndefined();
		});

		it("sets no class on code element when not provided", () => {
			const wrapper = mount();
			expect(wrapper.find("code").classes()).toHaveLength(0);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount();
			expect(wrapper.find("code").text()).toBe("const x = 1;");
		});
	});
});
