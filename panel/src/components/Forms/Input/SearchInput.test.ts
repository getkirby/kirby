import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import SearchInput from "./SearchInput.vue";
import StringInput from "./StringInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(SearchInput, {
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

describe("k-search-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-search-input");
		it.inheritsNoAttrs(mount);

		it("renders a k-string-input", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-string-input");
		});

		it("turns off autocomplete and spell checking", () => {
			const wrapper = mount({ autocomplete: "on", spellcheck: true });
			expect(wrapper.attributes("autocomplete")).toBe("off");
			expect(wrapper.attributes("spellcheck")).toBe("false");
		});
	});

	// props
	describe("placeholder prop", () => {
		it("defaults to the translated placeholder", () => {
			const wrapper = mount();
			expect(wrapper.attributes("placeholder")).toBe("search …");
		});

		it("sets the attribute", () => {
			const wrapper = mount({ placeholder: "Find pages …" });
			expect(wrapper.attributes("placeholder")).toBe("Find pages …");
		});
	});

	describe("type prop", () => {
		it("is always search", () => {
			const wrapper = mount({ type: "text" });
			expect(wrapper.attributes("type")).toBe("search");
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-string-input", () => {
			const wrapper = mount({ value: "kirby" });
			expect(wrapper.element.value).toBe("kirby");
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
			const wrapper = mount({ value: "kirby" });
			wrapper.vm.select();
			expect(wrapper.element.selectionStart).toBe(0);
			expect(wrapper.element.selectionEnd).toBe(5);
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-string-input", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("kirby");
			expect(wrapper.emitted("input")).toStrictEqual([["kirby"]]);
		});
	});
});
