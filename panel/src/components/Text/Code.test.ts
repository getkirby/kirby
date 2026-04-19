import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Code from "./Code.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Code, {
		props,
		attrs,
		slots: { default: "const x = 1;" }
	}).find("pre.k-code");
}

describe("Code.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "PRE", "k-code");
	});

	// props
	describe("language prop", () => {
		it("sets data-language attribute on pre", () => {
			const wrapper = mount({ language: "html" });
			expect(wrapper.attributes("data-language")).toBe("html");
		});

		it("sets language class on code element", () => {
			const wrapper = mount({ language: "html" });
			expect(wrapper.find("code").classes()).toContain("language-html");
		});

		it("omits data-language when not provided", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-language")).toBeUndefined();
		});

		it("sets no class on code element when not provided", () => {
			const wrapper = mount();
			expect(wrapper.find("code").classes()).toHaveLength(0);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = vueMount(Code, {
				slots: { default: "const x = 1;" }
			});
			expect(wrapper.find("code").text()).toBe("const x = 1;");
		});
	});
});
