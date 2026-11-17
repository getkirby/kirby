import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import preserveListeners from "@/mixins/preserveListeners";
import ColornameInput from "./ColornameInput.vue";
import StringInput from "./StringInput.vue";

function mount(props = {}, attrs = {}, attachTo: HTMLElement = document.body) {
	return vueMount(ColornameInput, {
		props,
		attrs,
		attachTo,
		global: {
			// registered globally in the Panel
			components: { "k-string-input": StringInput },
			directives: { direction: {} },
			// passes blur, keydown and paste on to the input
			mixins: [preserveListeners]
		}
	});
}

describe("k-colorname-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-colorname-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a k-string-input for text", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-string-input");
			expect(wrapper.attributes("type")).toBe("text");
		});

		it("turns off autocomplete and spell checking", () => {
			const wrapper = mount();
			expect(wrapper.attributes("autocomplete")).toBe("off");
			expect(wrapper.attributes("spellcheck")).toBe("false");
		});
	});

	// props
	describe("alpha prop", () => {
		it("keeps the alpha value by default", () => {
			const wrapper = mount();
			expect(wrapper.vm.convert("rgba(255, 0, 0, 0.5)")).toBe("#ff000080");
		});

		it("drops the alpha value", () => {
			const wrapper = mount({ alpha: false });
			expect(wrapper.vm.convert("rgba(255, 0, 0, 0.5)")).toBe("#ff0000");
		});
	});

	describe("format prop", () => {
		it.each([
			["hex", "#ff0000"],
			["rgb", "rgb(255 0 0)"],
			["hsl", "hsl(0 100% 50%)"]
		])("converts to %s", (format, expected) => {
			const wrapper = mount({ format });
			expect(wrapper.vm.convert("rgb(255, 0, 0)")).toBe(expected);
		});
	});

	describe("value prop", () => {
		it("shows the value", () => {
			const wrapper = mount({ value: "#ff0000" });
			expect(wrapper.element.value).toBe("#ff0000");
		});
	});

	// watch
	describe("value watcher", () => {
		it("marks a value that is no color as invalid", async () => {
			const wrapper = mount({ value: "#ff0000" });
			await wrapper.setProps({ value: "junk" } as never);
			expect(wrapper.element.validationMessage).toBe("error.validation.color");
		});

		it("marks a color as valid again", async () => {
			const wrapper = mount({ value: "junk" });
			await wrapper.setProps({ value: "#ff0000" } as never);
			expect(wrapper.element.validationMessage).toBe("");
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("marks a value that is no color as invalid", () => {
			const wrapper = mount({ value: "junk" });
			expect(wrapper.element.validationMessage).toBe("error.validation.color");
		});

		it("accepts an empty value", () => {
			const wrapper = mount({ value: "" });
			expect(wrapper.element.validationMessage).toBe("");
		});
	});

	// methods
	describe("convert()", () => {
		it("keeps an empty value", () => {
			const wrapper = mount();
			expect(wrapper.vm.convert("")).toBe("");
		});

		it("converts a color the library can parse", () => {
			const wrapper = mount();
			expect(wrapper.vm.convert("rgb(255, 0, 0)")).toBe("#ff0000");
		});

		// browsers compute an rgb() value for color names, happy-dom does not
		it("converts a color name via the browser", () => {
			const style = { color: "rgb(255, 0, 0)" } as CSSStyleDeclaration;
			vi.spyOn(window, "getComputedStyle").mockReturnValueOnce(style);
			const wrapper = mount();
			expect(wrapper.vm.convert("red")).toBe("#ff0000");
		});
	});

	describe("convertAndEmit()", () => {
		it("emits the converted color", () => {
			const wrapper = mount();
			wrapper.vm.convertAndEmit("rgb(255, 0, 0)");
			expect(wrapper.emitted("input")).toStrictEqual([["#ff0000"]]);
		});
	});

	describe("onSave()", () => {
		it("emits the converted color and submits the form", async () => {
			const form = document.createElement("form");
			document.body.append(form);
			const submit = vi
				.spyOn(form, "requestSubmit")
				.mockImplementation(() => {});
			const wrapper = mount({ value: "rgb(255, 0, 0)" }, {}, form);
			await wrapper.vm.onSave();
			expect(wrapper.emitted("input")).toStrictEqual([["#ff0000"]]);
			expect(submit).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("blur event", () => {
		it("emits the converted color", async () => {
			const wrapper = mount({ value: "rgb(255, 0, 0)" });
			await wrapper.find("input").trigger("blur");
			expect(wrapper.emitted("input")).toStrictEqual([["#ff0000"]]);
		});
	});

	describe("input event", () => {
		it("emits the typed value as is", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("rgb(255, 0, 0)");
			expect(wrapper.emitted("input")).toStrictEqual([["rgb(255, 0, 0)"]]);
		});
	});

	describe("keydown event", () => {
		it("converts the color on enter", async () => {
			const wrapper = mount({ value: "rgb(255, 0, 0)" });
			await wrapper.find("input").trigger("keydown.enter");
			expect(wrapper.emitted("input")).toStrictEqual([["#ff0000"]]);
		});

		it("converts the color on meta+s", async () => {
			const wrapper = mount({ value: "rgb(255, 0, 0)" });
			await wrapper.find("input").trigger("keydown.s", { metaKey: true });
			expect(wrapper.emitted("input")).toStrictEqual([["#ff0000"]]);
		});
	});

	describe("paste event", () => {
		it("emits the converted pasted color", async () => {
			const wrapper = mount();
			const data = new DataTransfer();
			data.setData("text/plain", "rgb(255, 0, 0)");
			const event = new ClipboardEvent("paste", { clipboardData: data });
			wrapper.find("input").element.dispatchEvent(event);
			expect(wrapper.emitted("input")).toStrictEqual([["#ff0000"]]);
		});
	});
});
