import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import StringInput from "./StringInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(StringInput, {
		props,
		attrs,
		// focus and selection only work on connected elements
		attachTo: document.body,
		global: {
			directives: { direction: {} }
		}
	});
}

describe("k-string-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-string-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe.each([
		["autocomplete", "email", "email"],
		["id", "title", "title"],
		["maxlength", 10, "10"],
		["minlength", 2, "2"],
		["name", "title", "title"],
		["pattern", "[a-z]+", "[a-z]+"],
		["placeholder", "Title …", "Title …"]
	])("%s prop", (prop, value, expected) => {
		it("sets the attribute", () => {
			const wrapper = mount({ [prop]: value });
			expect(wrapper.attributes(prop)).toBe(expected);
		});
	});

	describe("ariaLabel prop", () => {
		it("sets aria-label", () => {
			const wrapper = mount({ ariaLabel: "Title" });
			expect(wrapper.attributes("aria-label")).toBe("Title");
		});
	});

	describe("autofocus prop", () => {
		it("sets the attribute", () => {
			const wrapper = mount({ autofocus: true });
			expect(wrapper.attributes("autofocus")).toBe("");
		});
	});

	describe("disabled prop", () => {
		it("disables the input", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.element.disabled).toBe(true);
		});
	});

	describe("font prop", () => {
		it("sets data-font", () => {
			const wrapper = mount({ font: "monospace" });
			expect(wrapper.attributes("data-font")).toBe("monospace");
		});
	});

	describe("required prop", () => {
		it("requires a value", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.element.required).toBe(true);
		});
	});

	describe("spellcheck prop", () => {
		it("checks the spelling by default", () => {
			const wrapper = mount();
			expect(wrapper.attributes("spellcheck")).toBe("true");
		});

		it("turns off spell checking", () => {
			const wrapper = mount({ spellcheck: false });
			expect(wrapper.attributes("spellcheck")).toBe("false");
		});
	});

	describe("type prop", () => {
		it("defaults to text", () => {
			const wrapper = mount();
			expect(wrapper.attributes("type")).toBe("text");
		});

		it("sets the attribute", () => {
			const wrapper = mount({ type: "email" });
			expect(wrapper.attributes("type")).toBe("email");
		});
	});

	describe("value prop", () => {
		it("sets the value", () => {
			const wrapper = mount({ value: "abc" });
			expect(wrapper.element.value).toBe("abc");
		});

		it("updates the value", async () => {
			const wrapper = mount({ value: "abc" });
			await wrapper.setProps({ value: "def" } as never);
			expect(wrapper.element.value).toBe("def");
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("does not focus the input by default", () => {
			const wrapper = mount();
			expect(document.activeElement).not.toBe(wrapper.element);
		});

		it("focuses the input with autofocus", () => {
			const wrapper = mount({ autofocus: true });
			expect(document.activeElement).toBe(wrapper.element);
		});

		it("selects the value with preselect", () => {
			const wrapper = mount({ preselect: true, value: "abc" });
			expect(wrapper.element.selectionStart).toBe(0);
			expect(wrapper.element.selectionEnd).toBe(3);
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
		it("emits the typed value", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("abc");
			expect(wrapper.emitted("input")).toStrictEqual([["abc"]]);
		});
	});
});
