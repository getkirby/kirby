import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import CheckboxesInput from "./CheckboxesInput.vue";
import ChoiceInput from "./ChoiceInput.vue";

const options = [
	{ text: "A", value: "a" },
	{ text: "B", value: "b" },
	{ text: "C", value: "c" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(CheckboxesInput, {
		props: { id: "choice", options, ...props },
		attrs,
		// focus and clicks only work on connected elements
		attachTo: document.body,
		global: {
			// registered globally in the Panel
			components: { "k-choice-input": ChoiceInput }
		}
	});
}

function choices(wrapper: ReturnType<typeof mount>): Record<string, unknown>[] {
	return wrapper.vm.choices;
}

function checked(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("input").map((input) => input.element.checked);
}

function disabled(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("input").map((input) => input.element.disabled);
}

describe("k-checkboxes-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "FIELDSET", "k-checkboxes-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("has a legend for screen readers", () => {
			const wrapper = mount();
			const legend = wrapper.find("legend");
			expect(legend.classes()).toContain("sr-only");
			expect(legend.text()).toBe("options");
		});
	});

	// props
	describe("columns prop", () => {
		it("defaults to one column", () => {
			const wrapper = mount();
			expect(wrapper.find("ul").attributes("style")).toBe("--columns: 1;");
		});

		it("sets the number of columns", () => {
			const wrapper = mount({ columns: 3 });
			expect(wrapper.find("ul").attributes("style")).toBe("--columns: 3;");
		});
	});

	describe("disabled prop", () => {
		it("disables the fieldset and all choices", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.element.disabled).toBe(true);
			expect(disabled(wrapper)).toStrictEqual([true, true, true]);
		});
	});

	describe("max prop", () => {
		it("disables the unchecked choices once full", () => {
			const wrapper = mount({ max: 2, value: ["a", "b"] });
			expect(disabled(wrapper)).toStrictEqual([false, false, true]);
		});

		it("keeps all choices enabled below the maximum", () => {
			const wrapper = mount({ max: 2, value: ["a"] });
			expect(disabled(wrapper)).toStrictEqual([false, false, false]);
		});
	});

	describe("min prop", () => {
		it("passes the minimum to the k-input-validator", () => {
			const wrapper = mount({ min: 2 });
			expect(wrapper.find("k-input-validator").attributes("min")).toBe("2");
		});
	});

	describe("required prop", () => {
		it("passes the required state to the k-input-validator", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.find("k-input-validator").attributes("required")).toBe(
				"true"
			);
		});
	});

	describe("value prop", () => {
		it("checks the options in the value", () => {
			const wrapper = mount({ value: ["a", "c"] });
			expect(checked(wrapper)).toStrictEqual([true, false, true]);
		});

		it("passes the value to the k-input-validator", () => {
			const wrapper = mount({ value: ["a", "c"] });
			expect(wrapper.find("k-input-validator").attributes("value")).toBe(
				'["a","c"]'
			);
		});
	});

	// computed
	describe("choices computed", () => {
		it("turns the options into checkbox choices", () => {
			const wrapper = mount({ name: "letters", value: ["a"] });
			expect(wrapper.vm.choices[0]).toMatchObject({
				autofocus: false,
				checked: true,
				id: "choice-0",
				icon: null,
				label: "A",
				name: "letters",
				type: "checkbox",
				value: "a"
			});
		});

		it("autofocuses only the first choice", () => {
			const wrapper = mount({ autofocus: true });
			const autofocus = choices(wrapper).map((choice) => choice.autofocus);
			expect(autofocus).toStrictEqual([true, false, false]);
		});

		it("uses the id as name without a name", () => {
			const wrapper = mount();
			expect(wrapper.vm.choices[0].name).toBe("choice");
		});

		it("disables a disabled option", () => {
			const wrapper = mount({
				options: [{ disabled: true, text: "A", value: "a" }, options[1]]
			});
			expect(disabled(wrapper)).toStrictEqual([true, false]);
		});

		it("aligns options without icon with a blank icon", () => {
			const wrapper = mount({
				options: [{ icon: "heart", text: "A", value: "a" }, options[1]]
			});
			expect(wrapper.vm.choices[1].icon).toBe("blank");
		});
	});

	describe("isFull computed", () => {
		it("is full with as many values as the maximum", () => {
			const wrapper = mount({ max: 2, value: ["a", "b"] });
			expect(wrapper.vm.isFull).toBe(true);
		});

		it("is not full without a maximum", () => {
			const wrapper = mount({ value: ["a", "b", "c"] });
			expect(wrapper.vm.isFull).toBeFalsy();
		});
	});

	// watch
	describe("value watcher", () => {
		it("checks the options of a new value", async () => {
			const wrapper = mount({ value: ["a"] });
			await wrapper.setProps({ value: ["b"] } as never);
			expect(checked(wrapper)).toStrictEqual([false, true, false]);
		});

		it("checks nothing for a value that is no array", () => {
			const wrapper = mount({ value: "a" });
			expect(checked(wrapper)).toStrictEqual([false, false, false]);
		});
	});

	// methods
	describe("deselectAll()", () => {
		it("emits an empty selection", async () => {
			const wrapper = mount({ value: ["a", "b"] });
			wrapper.vm.deselectAll();
			await wrapper.vm.$nextTick();
			expect(wrapper.emitted("input")).toStrictEqual([[[]]]);
			expect(checked(wrapper)).toStrictEqual([false, false, false]);
		});
	});

	describe("focus()", () => {
		it("focuses the first choice", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(wrapper.find("input").element);
		});
	});

	describe("input()", () => {
		it("adds a checked option", () => {
			const wrapper = mount({ value: ["a"] });
			wrapper.vm.input("c", true);
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "c"]]]);
		});

		it("removes an unchecked option", () => {
			const wrapper = mount({ value: ["a", "c"] });
			wrapper.vm.input("a", false);
			expect(wrapper.emitted("input")).toStrictEqual([[["c"]]]);
		});
	});

	describe("select()", () => {
		it("focuses the first choice", () => {
			const wrapper = mount();
			wrapper.vm.select();
			expect(document.activeElement).toBe(wrapper.find("input").element);
		});
	});

	describe("selectAll()", () => {
		it("emits all options", () => {
			const wrapper = mount({ value: ["a"] });
			wrapper.vm.selectAll();
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "b", "c"]]]);
		});
	});

	// events
	describe("input event", () => {
		it("emits the selection when checking an option", () => {
			const wrapper = mount({ value: ["a"] });
			wrapper.findAll("input")[1].element.click();
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "b"]]]);
		});

		it("emits the selection when unchecking an option", () => {
			const wrapper = mount({ value: ["a", "b"] });
			wrapper.findAll("input")[0].element.click();
			expect(wrapper.emitted("input")).toStrictEqual([[["b"]]]);
		});
	});
});
