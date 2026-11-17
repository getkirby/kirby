import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import StringInput from "./StringInput.vue";
import UrlInput from "./UrlInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(UrlInput, {
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

describe("k-url-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-url-input");
		it.inheritsNoAttrs(mount);

		it("renders a k-string-input", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-string-input");
		});
	});

	// props
	describe("autocomplete prop", () => {
		it("defaults to url", () => {
			const wrapper = mount();
			expect(wrapper.attributes("autocomplete")).toBe("url");
		});

		it("sets the attribute", () => {
			const wrapper = mount({ autocomplete: "off" });
			expect(wrapper.attributes("autocomplete")).toBe("off");
		});
	});

	describe("placeholder prop", () => {
		it("defaults to the translated placeholder", () => {
			const wrapper = mount();
			expect(wrapper.attributes("placeholder")).toBe("url.placeholder");
		});

		it("sets the attribute", () => {
			const wrapper = mount({ placeholder: "https://…" });
			expect(wrapper.attributes("placeholder")).toBe("https://…");
		});
	});

	describe("type prop", () => {
		it("is always url", () => {
			const wrapper = mount({ type: "text" });
			expect(wrapper.attributes("type")).toBe("url");
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-string-input", () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			expect(wrapper.element.value).toBe("https://getkirby.com");
		});
	});

	// watch
	describe("value watcher", () => {
		it("marks an invalid URL as invalid", async () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			await wrapper.setProps({ value: "https://getkirby" } as never);
			expect(wrapper.element.validationMessage).toBe("error.validation.url");
		});

		it("marks a valid URL as valid again", async () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			await wrapper.setProps({ value: "https://getkirby" } as never);
			await wrapper.setProps({ value: "https://getkirby.com" } as never);
			expect(wrapper.element.validationMessage).toBe("");
		});

		it("accepts an empty value", async () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			await wrapper.setProps({ value: "" } as never);
			expect(wrapper.element.validationMessage).toBe("");
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
			const wrapper = mount({ value: "https://getkirby.com" });
			wrapper.vm.select();
			expect(wrapper.element.selectionStart).toBe(0);
			expect(wrapper.element.selectionEnd).toBe(20);
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-string-input", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("https://getkirby.com");
			expect(wrapper.emitted("input")).toStrictEqual([
				["https://getkirby.com"]
			]);
		});
	});
});
