import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import RangeInput from "./RangeInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(RangeInput, {
		props: { id: "range", ...props },
		attrs,
		// focus only works on connected elements
		attachTo: document.body
	});
}

function range(wrapper: ReturnType<typeof mount>) {
	return wrapper.find("input").element;
}

describe("k-range-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-range-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a range input", () => {
			const wrapper = mount();
			expect(range(wrapper).type).toBe("range");
		});
	});

	// props
	describe.each([
		["id", "amount", "id", "amount"],
		["name", "amount", "name", "amount"],
		["step", 5, "step", "5"]
	])("%s prop", (prop, value, attribute, expected) => {
		it("sets the attribute on the range", () => {
			const wrapper = mount({ [prop]: value });
			expect(wrapper.find("input").attributes(attribute)).toBe(expected);
		});
	});

	describe("default prop", () => {
		it("positions the slider without value", () => {
			const wrapper = mount({ default: 30 });
			expect(range(wrapper).value).toBe("30");
		});
	});

	describe("disabled prop", () => {
		it("disables the range", () => {
			const wrapper = mount({ disabled: true });
			expect(range(wrapper).disabled).toBe(true);
			expect(wrapper.attributes("data-disabled")).toBe("true");
		});
	});

	describe("max prop", () => {
		it("defaults to 100", () => {
			const wrapper = mount();
			expect(wrapper.find("input").attributes("max")).toBe("100");
		});
	});

	describe("min prop", () => {
		it("defaults to 0", () => {
			const wrapper = mount();
			expect(wrapper.find("input").attributes("min")).toBe("0");
		});
	});

	describe("tooltip prop", () => {
		it("shows the value in a tooltip", () => {
			const wrapper = mount({ value: 20 });
			const tooltip = wrapper.find("output");
			expect(tooltip.attributes("for")).toBe("range");
			expect(tooltip.find(".k-range-input-tooltip-text").text()).toBe("20");
		});

		it("adds text before and after the value", () => {
			const wrapper = mount({
				tooltip: { after: "%", before: "~" },
				value: 20
			});
			const tooltip = wrapper.find("output");
			expect(tooltip.find(".k-range-input-tooltip-before").text()).toBe("~");
			expect(tooltip.find(".k-range-input-tooltip-after").text()).toBe("%");
		});

		it("renders no tooltip when turned off", () => {
			const wrapper = mount({ tooltip: false });
			expect(wrapper.find("output").exists()).toBe(false);
		});
	});

	describe("value prop", () => {
		it("positions the slider", () => {
			const wrapper = mount({ value: 40 });
			expect(range(wrapper).value).toBe("40");
		});
	});

	// computed
	describe("baseline computed", () => {
		it("starts at the minimum", () => {
			const wrapper = mount({ min: 10 });
			expect(wrapper.vm.baseline).toBe(10);
		});

		it("starts at zero for a negative minimum", () => {
			const wrapper = mount({ min: -10 });
			expect(wrapper.vm.baseline).toBe(0);
		});
	});

	describe("isEmpty computed", () => {
		it.each([null, undefined, ""])("is empty for %o", (value) => {
			const wrapper = mount({ value });
			expect(wrapper.vm.isEmpty).toBe(true);
		});

		it("is not empty for zero", () => {
			const wrapper = mount({ value: 0 });
			expect(wrapper.vm.isEmpty).toBe(false);
		});
	});

	describe("label computed", () => {
		it("shows the value with the decimals of the step", () => {
			const wrapper = mount({ step: 0.5, value: 2 });
			expect(wrapper.vm.label).toBe("2.0");
		});

		it("shows zero", () => {
			const wrapper = mount({ value: 0 });
			expect(wrapper.vm.label).toBe("0");
		});

		it("shows a dash without value", () => {
			const wrapper = mount();
			expect(wrapper.vm.label).toBe("–");
		});

		it("shows the position without value when required", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.vm.label).toBe("0");
		});
	});

	describe("maxLength computed", () => {
		it("fits the longest formatted limit", () => {
			const wrapper = mount({ max: 1000, min: -5 });
			expect(wrapper.vm.maxLength).toBe(5);
		});
	});

	describe("position computed", () => {
		it("uses the value", () => {
			const wrapper = mount({ default: 30, value: 40 });
			expect(wrapper.vm.position).toBe(40);
		});

		it("uses zero as value", () => {
			const wrapper = mount({ default: 30, value: 0 });
			expect(wrapper.vm.position).toBe(0);
		});

		it("falls back to the default", () => {
			const wrapper = mount({ default: 30 });
			expect(wrapper.vm.position).toBe(30);
		});

		it("falls back to the baseline", () => {
			const wrapper = mount({ min: 10 });
			expect(wrapper.vm.position).toBe(10);
		});
	});

	describe("valuetext computed", () => {
		it("joins the tooltip texts and the label", () => {
			const wrapper = mount({
				tooltip: { after: "%", before: "~" },
				value: 20
			});
			expect(wrapper.find("input").attributes("aria-valuetext")).toBe("~ 20 %");
		});

		it("is null without tooltip", () => {
			const wrapper = mount({ tooltip: false });
			expect(wrapper.vm.valuetext).toBe(null);
		});
	});

	// watch
	describe("value watcher", () => {
		it("marks a missing required value as invalid", async () => {
			const wrapper = mount({ required: true, value: 5 });
			await wrapper.setProps({ value: "" } as never);
			expect(range(wrapper).validationMessage).toBe(
				"error.validation.required"
			);
		});

		it("marks a value below the minimum as invalid", async () => {
			const wrapper = mount({ min: 10, value: 20 });
			await wrapper.setProps({ value: 5 } as never);
			expect(range(wrapper).validationMessage).toBe("error.validation.min");
		});

		it("marks a value above the maximum as invalid", async () => {
			const wrapper = mount({ max: 10, value: 5 });
			await wrapper.setProps({ value: 20 } as never);
			expect(range(wrapper).validationMessage).toBe("error.validation.max");
		});

		it("marks a valid value as valid again", async () => {
			const wrapper = mount({ max: 10, value: 5 });
			await wrapper.setProps({ value: 20 } as never);
			await wrapper.setProps({ value: 8 } as never);
			expect(range(wrapper).validationMessage).toBe("");
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("focuses the range with autofocus", () => {
			const wrapper = mount({ autofocus: true });
			expect(document.activeElement).toBe(range(wrapper));
		});

		it("marks a missing required value as invalid", () => {
			const wrapper = mount({ required: true });
			expect(range(wrapper).validationMessage).toBe(
				"error.validation.required"
			);
		});

		it("marks a value below the minimum as invalid", () => {
			const wrapper = mount({ min: 10, value: 5 });
			expect(range(wrapper).validationMessage).toBe("error.validation.min");
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the range", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(range(wrapper));
		});
	});

	describe("format()", () => {
		it("formats the number with the decimals of the step", () => {
			const wrapper = mount({ step: 0.25 });
			expect(wrapper.vm.format(1)).toBe("1.00");
		});

		it("formats whole numbers for a whole step", () => {
			const wrapper = mount();
			expect(wrapper.vm.format(1000)).toBe("1,000");
		});
	});

	// events
	describe("input event", () => {
		it("emits the number of the slider", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("25");
			expect(wrapper.emitted("input")).toStrictEqual([[25]]);
		});
	});
});
