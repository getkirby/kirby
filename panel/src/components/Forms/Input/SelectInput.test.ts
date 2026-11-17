import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import SelectInput from "./SelectInput.vue";

const options = [
	{ text: "A", value: "a" },
	{ text: "B", value: "b" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(SelectInput, {
		props: { options, ...props },
		attrs,
		// focus only works on connected elements
		attachTo: document.body
	});
}

function texts(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("option").map((option) => option.text());
}

describe("k-select-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "SPAN", "k-select-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe.each([
		["ariaLabel", "Letter", "aria-label", "Letter"],
		["id", "letter", "id", "letter"],
		["name", "letter", "name", "letter"]
	])("%s prop", (prop, value, attribute, expected) => {
		it("sets the attribute on the select", () => {
			const wrapper = mount({ [prop]: value });
			expect(wrapper.find("select").attributes(attribute)).toBe(expected);
		});
	});

	describe("autofocus prop", () => {
		it("sets the attribute on the select", () => {
			const wrapper = mount({ autofocus: true });
			expect(wrapper.find("select").attributes("autofocus")).toBe("");
		});
	});

	describe("disabled prop", () => {
		it("disables the select", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.find("select").element.disabled).toBe(true);
			expect(wrapper.attributes("data-disabled")).toBe("true");
		});
	});

	describe("options prop", () => {
		it("renders an option for each option after the empty one", () => {
			const wrapper = mount();
			expect(texts(wrapper)).toStrictEqual(["—", "A", "B"]);
		});

		it("disables a disabled option", () => {
			const wrapper = mount({
				options: [{ disabled: true, text: "A", value: "a" }]
			});
			expect(wrapper.findAll("option")[1].element.disabled).toBe(true);
		});
	});

	describe("placeholder prop", () => {
		it("labels the empty option", () => {
			const wrapper = mount({ placeholder: "Pick a letter" });
			expect(texts(wrapper)[0]).toBe("Pick a letter");
		});
	});

	describe("required prop", () => {
		it("requires the select", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.find("select").element.required).toBe(true);
		});
	});

	describe("value prop", () => {
		it("selects the option with the value", () => {
			const wrapper = mount({ value: "b" });
			expect(wrapper.find("select").element.value).toBe("b");
		});

		it("marks an empty value", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-empty")).toBe("true");
		});
	});

	// computed
	describe("empty computed", () => {
		it("defaults to a dash", () => {
			const wrapper = mount();
			expect(wrapper.vm.empty).toBe("—");
		});

		it("uses the placeholder", () => {
			const wrapper = mount({ placeholder: "Pick a letter" });
			expect(wrapper.vm.empty).toBe("Pick a letter");
		});
	});

	describe("hasEmptyOption computed", () => {
		it("offers the empty option when not required", () => {
			const wrapper = mount({ value: "a" });
			expect(wrapper.vm.hasEmptyOption).toBe(true);
		});

		it("offers a disabled empty option when required without value", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.vm.hasEmptyOption).toBe(true);
			expect(wrapper.find("option").element.disabled).toBe(true);
		});

		it("hides the empty option when required with a value", () => {
			const wrapper = mount({ required: true, value: "a" });
			expect(wrapper.vm.hasEmptyOption).toBe(false);
			expect(texts(wrapper)).toStrictEqual(["A", "B"]);
		});
	});

	describe("isEmpty computed", () => {
		it.each([null, undefined, ""])("is empty for %o", (value) => {
			const wrapper = mount({ value });
			expect(wrapper.vm.isEmpty).toBe(true);
		});

		it("is not empty with a value", () => {
			const wrapper = mount({ value: "a" });
			expect(wrapper.vm.isEmpty).toBe(false);
		});
	});

	describe("label computed", () => {
		it("shows the text of the selected option", () => {
			const wrapper = mount({ value: "b" });
			expect(wrapper.vm.label).toBe("B");
		});

		it("shows the empty text without value", () => {
			const wrapper = mount({ placeholder: "Pick a letter" });
			expect(wrapper.vm.label).toBe("Pick a letter");
		});

		it("shows the empty text for an unknown value", () => {
			const wrapper = mount({ value: "x" });
			expect(wrapper.vm.label).toBe("—");
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("focuses the select with autofocus", () => {
			const wrapper = mount({ autofocus: true });
			expect(document.activeElement).toBe(wrapper.find("select").element);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the select", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(wrapper.find("select").element);
		});
	});

	describe("select()", () => {
		it("focuses the select", () => {
			const wrapper = mount();
			wrapper.vm.select();
			expect(document.activeElement).toBe(wrapper.find("select").element);
		});
	});

	describe("text()", () => {
		it("returns the text of the option with the value", () => {
			const wrapper = mount();
			expect(wrapper.vm.text("b")).toBe("B");
		});

		it("matches numbers and their strings", () => {
			const wrapper = mount({ options: [{ text: "One", value: "1" }] });
			expect(wrapper.vm.text(1)).toBe("One");
		});

		it("returns null for an unknown value", () => {
			const wrapper = mount();
			expect(wrapper.vm.text("x")).toBe(null);
		});
	});

	// events
	describe("click event", () => {
		it("emits the click without letting it bubble", async () => {
			const wrapper = mount();
			let bubbled = false;
			wrapper.element.addEventListener("click", () => (bubbled = true));
			await wrapper.find("select").trigger("click");
			expect(wrapper.emitted("click")).toHaveLength(1);
			expect(bubbled).toBe(false);
		});
	});

	describe("input event", () => {
		it("emits the selected value", async () => {
			const wrapper = mount();
			await wrapper.find("select").setValue("b");
			expect(wrapper.emitted("input")).toStrictEqual([["b"]]);
		});
	});
});
