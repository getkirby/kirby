import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ColorField from "./ColorField.vue";

const Input = {
	name: "k-input",
	props: ["type", "value"],
	template: `<div>
		<slot name="before" />
		<slot />
		<div class="after"><slot name="after" /></div>
		<div class="icon"><slot name="icon" /></div>
	</div>`
};
const Dropdown = {
	name: "k-dropdown",
	methods: { toggle: vi.fn() },
	template: "<div><slot /></div>"
};
const Name = {
	name: "k-colorname-input",
	props: ["alpha", "format", "value"],
	emits: ["input"],
	template: "<input />"
};
const Options = {
	name: "k-coloroptions-input",
	props: ["options", "value"],
	emits: ["input"],
	template: "<div />"
};
const Picker = {
	name: "k-colorpicker-input",
	props: ["options", "value"],
	emits: ["input"],
	template: "<div />"
};
const options = [
	{ text: "Red", value: "rgb(255, 0, 0)" },
	{ text: "Blue", value: "#0000ff" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(ColorField, {
		props,
		attrs,
		global: {
			stubs: {
				"k-colorname-input": Name,
				"k-coloroptions-input": Options,
				"k-colorpicker-input": Picker,
				"k-dropdown": Dropdown,
				"k-input": Input
			}
		}
	});
}

describe("k-color-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-color-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("disabled prop", () => {
		it("disables the picker toggle and hides the icon button", () => {
			const wrapper = mount({ disabled: true });
			const toggle = wrapper.find<HTMLButtonElement>(
				".k-color-field-picker-toggle"
			);
			expect(toggle.element.disabled).toBe(true);
			expect(wrapper.find(".icon k-button").exists()).toBe(false);
		});
	});

	describe("icon prop", () => {
		it("defaults to pipette", () => {
			const wrapper = mount();
			expect(wrapper.find(".icon k-button").attributes("icon")).toBe("pipette");
		});

		it("sets the icon of the picker button", () => {
			const wrapper = mount({ icon: "palette" });
			expect(wrapper.find(".icon k-button").attributes("icon")).toBe("palette");
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the input", () => {
			const wrapper = mount({ id: "color" });
			expect(wrapper.attributes("input")).toBe("color");
		});
	});

	describe("mode prop", () => {
		it("renders a picker by default", () => {
			const wrapper = mount({ value: "#ff0000" });
			const toggle = wrapper.find(".k-color-field-picker-toggle");
			expect(toggle.find("k-color-frame").attributes("color")).toBe("#ff0000");
			expect(wrapper.findComponent(Picker).exists()).toBe(true);
			expect(wrapper.findComponent(Name).exists()).toBe(true);
			expect(wrapper.findComponent(Input).props("type")).toBe("color");
		});

		it("renders an input with a preview", () => {
			const wrapper = mount({ mode: "input", value: "#ff0000" });
			expect(wrapper.find("k-color-frame").attributes("color")).toBe("#ff0000");
			expect(wrapper.find(".k-color-field-picker-toggle").exists()).toBe(false);
			expect(wrapper.findComponent(Picker).exists()).toBe(false);
			expect(wrapper.find(".icon k-button").exists()).toBe(false);
			expect(wrapper.findComponent(Name).exists()).toBe(true);
		});

		it("renders the options", () => {
			const wrapper = mount({ mode: "options", options });
			expect(wrapper.findComponent(Options).props("options")).toStrictEqual([
				{ text: "Red", value: "#ff0000" },
				{ text: "Blue", value: "#0000ff" }
			]);
			expect(wrapper.findComponent(Input).exists()).toBe(false);
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-colorname-input", () => {
			const wrapper = mount({ value: "#ff0000" });
			expect(wrapper.findComponent(Name).props("value")).toBe("#ff0000");
		});
	});

	// computed
	describe("convertedOptions computed", () => {
		it("converts the option values to the format", () => {
			const wrapper = mount({ format: "rgb", options });
			expect(wrapper.vm.convertedOptions).toStrictEqual([
				{ text: "Red", value: "rgb(255 0 0)" },
				{ text: "Blue", value: "rgb(0 0 255)" }
			]);
		});

		it("passes the converted options to the picker", () => {
			const wrapper = mount({ options });
			expect(wrapper.findComponent(Picker).props("options")).toStrictEqual(
				wrapper.vm.convertedOptions
			);
		});
	});

	describe("currentOption computed", () => {
		it("shows the text of the option with the value", () => {
			const wrapper = mount({ options, value: "#ff0000" });
			expect(wrapper.vm.currentOption).toStrictEqual({
				text: "Red",
				value: "#ff0000"
			});
			expect(wrapper.find(".after").text()).toBe("Red");
		});

		it("shows no text for a value without option", () => {
			const wrapper = mount({ options, value: "#00ff00" });
			expect(wrapper.vm.currentOption).toBeUndefined();
			expect(wrapper.find(".after").text()).toBe("");
		});
	});

	// methods
	describe("convert()", () => {
		it("converts a color to the format", () => {
			const wrapper = mount({ format: "hsl" });
			expect(wrapper.vm.convert("#ff0000")).toBe("hsl(0 100% 50%)");
		});

		it("drops the alpha value when turned off", () => {
			const wrapper = mount({ alpha: false });
			expect(wrapper.vm.convert("rgba(255, 0, 0, 0.5)")).toBe("#ff0000");
		});
	});

	// events
	describe("click event", () => {
		it("toggles the picker with the preview", async () => {
			const wrapper = mount();
			await wrapper.find(".k-color-field-picker-toggle").trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});

		it("toggles the picker with the icon button", async () => {
			const wrapper = mount();
			await wrapper.find(".icon k-button").trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});
	});

	describe("input event", () => {
		it.each([
			["k-colorname-input", Name, {}],
			["k-colorpicker-input", Picker, {}],
			["k-coloroptions-input", Options, { mode: "options" }]
		])("emits the value of the %s", (_, component, props) => {
			const wrapper = mount(props);
			wrapper.findComponent(component).vm.$emit("input", "#ff0000");
			expect(wrapper.emitted("input")).toStrictEqual([["#ff0000"]]);
		});
	});
});
