import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ChoiceInput from "./ChoiceInput.vue";
import RadioInput from "./RadioInput.vue";

const options = [
	{ text: "A", value: "a" },
	{ text: "B", value: "b" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(RadioInput, {
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

describe("k-radio-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "FIELDSET", "k-radio-input");
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
			expect(choices(wrapper).every((choice) => choice.disabled)).toBe(true);
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
		it("checks the option with the value", () => {
			const wrapper = mount({ value: "b" });
			const inputs = wrapper.findAll("input");
			expect(inputs.map((input) => input.element.checked)).toStrictEqual([
				false,
				true
			]);
		});

		it("passes the value to the k-input-validator", () => {
			const wrapper = mount({ value: "b" });
			expect(wrapper.find("k-input-validator").attributes("value")).toBe('"b"');
		});
	});

	// computed
	describe("choices computed", () => {
		it("turns the options into radio choices", () => {
			const wrapper = mount({ name: "letter", value: "a" });
			expect(wrapper.vm.choices[0]).toMatchObject({
				autofocus: false,
				checked: true,
				id: "choice-0",
				icon: null,
				label: "A",
				name: "letter",
				type: "radio",
				value: "a"
			});
		});

		it("autofocuses only the first choice", () => {
			const wrapper = mount({ autofocus: true });
			expect(choices(wrapper).map((choice) => choice.autofocus)).toStrictEqual([
				true,
				false
			]);
		});

		it("uses the id as name without a name", () => {
			const wrapper = mount();
			expect(wrapper.vm.choices[0].name).toBe("choice");
		});

		it("disables a disabled option", () => {
			const wrapper = mount({
				options: [{ disabled: true, text: "A", value: "a" }, options[1]]
			});
			const disabled = choices(wrapper).map((choice) => choice.disabled);
			expect(disabled.map((state) => state === true)).toStrictEqual([
				true,
				false
			]);
		});

		it("passes info, icon and theme of an option", () => {
			const wrapper = mount({
				options: [
					{
						icon: "draft",
						info: "Info",
						text: "A",
						theme: "negative",
						value: "a"
					}
				]
			});
			expect(wrapper.vm.choices[0]).toMatchObject({
				icon: "draft",
				info: "Info",
				theme: "negative"
			});
		});

		it("aligns options without icon with a blank icon", () => {
			const wrapper = mount({
				options: [{ icon: "draft", text: "A", value: "a" }, options[1]]
			});
			expect(wrapper.vm.choices[1].icon).toBe("blank");
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the first choice", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(wrapper.find("input").element);
		});
	});

	describe("select()", () => {
		it("focuses the first choice", () => {
			const wrapper = mount();
			wrapper.vm.select();
			expect(document.activeElement).toBe(wrapper.find("input").element);
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the chosen option", () => {
			const wrapper = mount({ value: "a" });
			wrapper.findAll("input")[1].element.click();
			expect(wrapper.emitted("input")).toStrictEqual([["b"]]);
		});
	});
});
