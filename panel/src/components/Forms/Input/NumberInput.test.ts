import { describe, expect, it, onTestFinished, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import NumberInput from "./NumberInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(NumberInput, {
		props,
		attrs,
		// focus only works on connected elements
		attachTo: document.body
	});
}

describe("k-number-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-number-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a number input", () => {
			const wrapper = mount();
			expect(wrapper.attributes("type")).toBe("number");
		});
	});

	// props
	describe.each([
		["id", "amount", "amount"],
		["max", 10, "10"],
		["min", 2, "2"],
		["name", "amount", "amount"],
		["placeholder", "0", "0"],
		["step", 0.5, "0.5"]
	])("%s prop", (prop, value, expected) => {
		it("sets the attribute", () => {
			const wrapper = mount({ [prop]: value });
			expect(wrapper.attributes(prop)).toBe(expected);
		});
	});

	describe("disabled prop", () => {
		it("disables the input", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.element.disabled).toBe(true);
		});
	});

	describe("required prop", () => {
		it("requires the input", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.element.required).toBe(true);
		});
	});

	describe("value prop", () => {
		it("shows the value with the decimals of the step", () => {
			const wrapper = mount({ step: 0.01, value: 1.5 });
			expect(wrapper.element.value).toBe("1.50");
		});

		it("shows nothing for a value that is no number", () => {
			const wrapper = mount({ value: "abc" });
			expect(wrapper.element.value).toBe("");
		});
	});

	// watch
	describe("value watcher", () => {
		it("shows a new value", async () => {
			const wrapper = mount({ value: 1 });
			await wrapper.setProps({ value: 3 } as never);
			expect(wrapper.element.value).toBe("3");
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("focuses the input with autofocus", () => {
			const wrapper = mount({ autofocus: true });
			expect(document.activeElement).toBe(wrapper.element);
		});

		it("selects the value with preselect", () => {
			const select = vi.spyOn(HTMLInputElement.prototype, "select");
			onTestFinished(() => select.mockRestore());
			mount({ preselect: true, value: 5 });
			expect(select).toHaveBeenCalledOnce();
		});
	});

	// methods
	describe("clean()", () => {
		it("formats the shown number", async () => {
			const wrapper = mount({ step: 0.01 });
			await wrapper.find("input").setValue("1.5");
			wrapper.vm.clean();
			await wrapper.vm.$nextTick();
			expect(wrapper.element.value).toBe("1.50");
		});
	});

	describe("decimals()", () => {
		it.each([
			[undefined, 0],
			[1, 0],
			[0.5, 1],
			[0.01, 2],
			["0.001", 3],
			[1e-7, 7]
		])("counts the decimals of step %o", (step, decimals) => {
			const wrapper = mount({ step });
			expect(wrapper.vm.decimals()).toBe(decimals);
		});
	});

	describe("emit()", () => {
		it("emits the number", () => {
			const wrapper = mount();
			wrapper.vm.emit("2.5");
			expect(wrapper.emitted("input")).toStrictEqual([[2.5]]);
		});

		it("emits an empty string for no number", () => {
			const wrapper = mount({ value: 1 });
			wrapper.vm.emit("abc");
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});

		it("emits nothing for the current value", () => {
			const wrapper = mount({ value: 2 });
			wrapper.vm.emit("2");
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("focus()", () => {
		it("focuses the input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(wrapper.element);
		});
	});

	describe("format()", () => {
		it("returns an empty string for no number", () => {
			const wrapper = mount();
			expect(wrapper.vm.format("")).toBe("");
			expect(wrapper.vm.format("abc")).toBe("");
		});

		it("keeps the value for any step", () => {
			const wrapper = mount({ step: "any" });
			expect(wrapper.vm.format("1.2345")).toBe("1.2345");
		});

		it("pads the decimals of the step", () => {
			const wrapper = mount({ step: 0.1 });
			expect(wrapper.vm.format(2)).toBe("2.0");
		});

		it("drops the decimals for a whole step", () => {
			const wrapper = mount({ step: 1 });
			expect(wrapper.vm.format("2.2")).toBe(2);
		});

		it("keeps the decimals without a step", () => {
			const wrapper = mount();
			expect(wrapper.vm.format("2.25")).toBe(2.25);
		});
	});

	describe("onBlur()", () => {
		it("formats and emits the number", async () => {
			const wrapper = mount({ step: 1 });
			await wrapper.find("input").setValue("2.2");
			wrapper.vm.onBlur();
			await wrapper.vm.$nextTick();
			expect(wrapper.element.value).toBe("2");
			expect(wrapper.emitted("input")?.at(-1)).toStrictEqual([2]);
		});
	});

	describe("onInput()", () => {
		it("emits the number", () => {
			const wrapper = mount();
			wrapper.vm.onInput("4");
			expect(wrapper.emitted("input")).toStrictEqual([[4]]);
		});
	});

	describe("select()", () => {
		it("selects the input", () => {
			const wrapper = mount({ value: 5 });
			const select = vi.spyOn(wrapper.element, "select");
			wrapper.vm.select();
			expect(select).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("blur event", () => {
		it("formats the number", async () => {
			const wrapper = mount({ step: 0.01 });
			await wrapper.find("input").setValue("1.5");
			await wrapper.find("input").trigger("blur");
			expect(wrapper.element.value).toBe("1.50");
		});
	});

	describe("input event", () => {
		it("emits the typed number", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("5");
			expect(wrapper.emitted("input")).toStrictEqual([[5]]);
		});
	});

	describe("keydown event", () => {
		it.each([["ctrl"], ["meta"]])("formats the number on %s+s", async (key) => {
			const wrapper = mount({ step: 0.01 });
			await wrapper.find("input").setValue("1.5");
			await wrapper.find("input").trigger("keydown.s", { [key + "Key"]: true });
			expect(wrapper.element.value).toBe("1.50");
		});
	});
});
