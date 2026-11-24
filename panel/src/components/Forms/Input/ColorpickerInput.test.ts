import { describe, expect, it, onTestFinished, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ColorpickerInput from "./ColorpickerInput.vue";

function input(name: string, extra: Record<string, unknown> = {}) {
	return {
		name,
		props: ["autofocus", "disabled", "format", "options", "required", "value"],
		emits: ["input"],
		template: "<div />",
		...extra
	};
}

const Alpha = input("k-alpha-input");
const Coloroptions = input("k-coloroptions-input");
const Coords = input("k-coords-input", { methods: { focus: vi.fn() } });
const Hue = input("k-hue-input");

function mount(props = {}, attrs = {}) {
	return vueMount(ColorpickerInput, {
		props: { value: "#ff0000", ...props },
		attrs,
		global: {
			stubs: {
				"k-alpha-input": Alpha,
				"k-coloroptions-input": Coloroptions,
				"k-coords-input": Coords,
				"k-hue-input": Hue
			}
		}
	});
}

function lastInput(wrapper: ReturnType<typeof mount>) {
	return wrapper.emitted("input")?.at(-1)?.[0];
}

describe("k-colorpicker-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "FIELDSET", "k-colorpicker-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("names the fieldset for screen readers", () => {
			expect(mount().find("legend.sr-only").text()).toBe("color");
		});

		it("sets the color as custom properties", () => {
			const fieldset = mount().element as HTMLElement;
			expect(fieldset.style.getPropertyValue("--h")).toBe("0");
			expect(fieldset.style.getPropertyValue("--s")).toBe("100%");
			expect(fieldset.style.getPropertyValue("--l")).toBe("50%");
			expect(fieldset.style.getPropertyValue("--a")).toBe("1");
		});

		it("keeps the formatted color in a hidden input", () => {
			const hidden = mount().find("input");
			expect(hidden.attributes("aria-hidden")).toBe("true");
			expect(hidden.attributes("tabindex")).toBe("-1");
			expect(hidden.element.value).toBe("#ff0000");
		});

		it("labels the hue and alpha sliders", () => {
			const labels = mount().findAll("label");
			expect(
				labels.map((label) => label.attributes("aria-label"))
			).toStrictEqual(["hue", "alpha"]);
		});
	});

	// props
	describe("alpha prop", () => {
		it("hides the alpha slider", () => {
			const wrapper = mount({ alpha: false });
			expect(wrapper.findComponent(Alpha).exists()).toBe(false);
		});

		it("keeps the color opaque", () => {
			const wrapper = mount({ alpha: false });
			wrapper.vm.setAlpha(0.5);
			expect(wrapper.vm.color.a).toBe(1);
		});
	});

	describe("autofocus prop", () => {
		it("autofocuses the coordinates", () => {
			const wrapper = mount({ autofocus: true });
			expect(wrapper.findComponent(Coords).props("autofocus")).toBe(true);
		});
	});

	describe("disabled prop", () => {
		it("disables all inputs", () => {
			const wrapper = mount({ disabled: true });
			expect(
				[Coords, Hue, Alpha, Coloroptions].map((component) =>
					wrapper.findComponent(component).props("disabled")
				)
			).toStrictEqual([true, true, true, true]);
		});
	});

	describe("format prop", () => {
		it("emits the color in the format", () => {
			const wrapper = mount({ format: "rgb" });
			wrapper.vm.setHue(120);
			expect(lastInput(wrapper)).toBe("rgb(0 255 0)");
		});

		it("formats the value", () => {
			const wrapper = mount({ format: "hsl" });
			expect(wrapper.find("input").element.value).toBe("hsl(0 100% 50%)");
		});
	});

	describe("name prop", () => {
		it("names the hidden input", () => {
			const wrapper = mount({ name: "color" });
			expect(wrapper.find("input").attributes("name")).toBe("color");
		});
	});

	describe("options prop", () => {
		it("offers the options", () => {
			const options = [{ text: "Red", value: "#ff0000" }];
			const wrapper = mount({ format: "rgb", options });
			expect(wrapper.findComponent(Coloroptions).props()).toStrictEqual({
				autofocus: undefined,
				disabled: false,
				format: "rgb",
				options,
				required: false,
				value: "#ff0000"
			});
		});
	});

	describe("required prop", () => {
		it("requires all inputs", () => {
			const wrapper = mount({ required: true });
			expect(
				[Coords, Hue, Alpha, Coloroptions].map((component) =>
					wrapper.findComponent(component).props("required")
				)
			).toStrictEqual([true, true, true, true]);
			expect(wrapper.find("input").attributes("required")).toBe("");
		});
	});

	describe("value prop", () => {
		it("places the color", () => {
			const wrapper = mount({ value: "#80ff8080" });
			expect(wrapper.findComponent(Coords).props("value")).toStrictEqual({
				x: 49.80392156862745,
				y: 0
			});
			expect(wrapper.findComponent(Hue).props("value")).toBe(120);
			expect(wrapper.findComponent(Alpha).props("value")).toBeCloseTo(0.5, 2);
		});

		it("formats a value in another notation", () => {
			const wrapper = mount({ value: "rgb(0, 0, 255)" });
			expect(wrapper.find("input").element.value).toBe("#0000ff");
		});

		it("resets without a valid value", () => {
			const wrapper = mount({ value: "nope" });
			expect(wrapper.vm.color).toStrictEqual({ a: 1, h: 0, s: 0, v: 1 });
			expect(wrapper.find("input").element.value).toBe("");
		});

		it("has no coordinates without value", () => {
			const wrapper = mount({ value: null });
			expect(wrapper.findComponent(Coords).props("value")).toBe(null);
		});
	});

	// computed
	describe("hsl computed", () => {
		it("falls back to black when the color can't be converted", () => {
			const wrapper = mount();
			const convert = vi.spyOn(wrapper.vm.$library.colors, "convert");
			convert.mockImplementation(() => {
				throw new Error("nope");
			});
			onTestFinished(() => convert.mockRestore());
			wrapper.vm.color = { a: 0.5, h: 10, s: 0.5, v: 0.5 };
			expect(wrapper.vm.hsl).toStrictEqual({ a: 1, h: 0, l: "0%", s: "0%" });
		});
	});

	// watch
	describe("value watcher", () => {
		it("keeps the hue of its own emitted value", async () => {
			// black has no hue, parsing it again would drop it
			const wrapper = mount({ value: "#000" });
			wrapper.vm.setHue(120);
			expect(lastInput(wrapper)).toBe("#000000");
			await wrapper.setProps({ value: lastInput(wrapper) } as never);
			expect(wrapper.vm.color.h).toBe(120);
		});

		it("reads a new value", async () => {
			const wrapper = mount();
			await wrapper.setProps({ value: "#0000ff" } as never);
			expect(wrapper.vm.color.h).toBe(240);
		});
	});

	// methods
	describe("between()", () => {
		it("keeps the value between min and max", () => {
			const wrapper = mount();
			expect(wrapper.vm.between(5, 0, 1)).toBe(1);
			expect(wrapper.vm.between(-5, 0, 1)).toBe(0);
			expect(wrapper.vm.between(0.5, 0, 1)).toBe(0.5);
		});
	});

	describe("focus()", () => {
		it("focuses the coordinates", () => {
			const wrapper = mount();
			const focus = vi.spyOn(wrapper.findComponent(Coords).vm, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	describe("setAlpha()", () => {
		it("emits the color with the alpha", () => {
			const wrapper = mount();
			wrapper.vm.setAlpha("0.5");
			expect(lastInput(wrapper)).toBe("#ff000080");
		});

		it("keeps the alpha between 0 and 1", () => {
			const wrapper = mount();
			wrapper.vm.setAlpha(2);
			expect(wrapper.vm.color.a).toBe(1);
		});
	});

	describe("setCoords()", () => {
		it("emits the color with the saturation and brightness", () => {
			const wrapper = mount();
			wrapper.vm.setCoords({ x: 50, y: 50 });
			expect(wrapper.vm.color).toMatchObject({ s: 0.5, v: 0.5 });
			expect(lastInput(wrapper)).toBe("#7f3f3f");
		});

		it("rounds the coordinates", () => {
			const wrapper = mount();
			wrapper.vm.setCoords({ x: 49.6, y: 0.4 });
			expect(wrapper.vm.color).toMatchObject({ s: 0.5, v: 1 });
		});

		it("emits an empty value without coordinates", () => {
			const wrapper = mount();
			wrapper.vm.setCoords(null);
			expect(lastInput(wrapper)).toBe("");
		});
	});

	describe("setHue()", () => {
		it("emits the color with the hue", () => {
			const wrapper = mount();
			wrapper.vm.setHue("240");
			expect(lastInput(wrapper)).toBe("#0000ff");
		});

		it("keeps the hue between 0 and 360", () => {
			const wrapper = mount();
			wrapper.vm.setHue(400);
			expect(wrapper.vm.color.h).toBe(360);
		});
	});

	// events
	describe("events", () => {
		it("emits the color from the coordinates", () => {
			const wrapper = mount();
			wrapper.findComponent(Coords).vm.$emit("input", { x: 0, y: 100 });
			expect(lastInput(wrapper)).toBe("#000000");
		});

		it("emits the color from the hue", () => {
			const wrapper = mount();
			wrapper.findComponent(Hue).vm.$emit("input", 120);
			expect(lastInput(wrapper)).toBe("#00ff00");
		});

		it("emits the color from the alpha", () => {
			const wrapper = mount();
			wrapper.findComponent(Alpha).vm.$emit("input", 0);
			expect(lastInput(wrapper)).toBe("#ff000000");
		});

		it("emits an option as it is", () => {
			const wrapper = mount();
			wrapper.findComponent(Coloroptions).vm.$emit("input", "#00f");
			expect(lastInput(wrapper)).toBe("#00f");
		});
	});
});
