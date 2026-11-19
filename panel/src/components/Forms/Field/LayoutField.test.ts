import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import LayoutField from "./LayoutField.vue";

const Button = {
	name: "k-button",
	props: ["autofocus", "icon", "text", "title"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)" />`
};
const Dropdown = {
	name: "k-dropdown",
	props: ["options"],
	methods: { toggle: vi.fn() },
	template: "<div />"
};
const Field = {
	name: "k-field",
	template: `<div><div class="options"><slot name="options" /></div><slot /></div>`
};
const Layouts = {
	name: "k-layouts",
	props: ["fieldsets", "value"],
	emits: ["input"],
	methods: {
		copy: vi.fn(),
		pasteboard: vi.fn(),
		removeAll: vi.fn(),
		select: vi.fn()
	},
	template: "<div />"
};

const fieldsets = { text: { name: "Text", type: "text" } };
const layouts = [{ columns: [], id: "a" }];

function mount(props = {}, attrs = {}, stubs = {}) {
	return vueMount(LayoutField, {
		props: { fieldsets, value: layouts, ...props },
		attrs,
		global: {
			stubs: {
				"k-button": Button,
				"k-dropdown": Dropdown,
				"k-layouts": Layouts,
				...stubs
			}
		}
	});
}

// a literal <k-field> tag only renders the default slot
function field(props = {}) {
	return mount(props, {}, { "k-field": Field });
}

describe("k-layout-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-layout-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("autofocus prop", () => {
		it("focuses the add button", () => {
			const wrapper = field({ autofocus: true });
			const [add] = wrapper.find(".options").findAllComponents(Button);
			expect(add.props("autofocus")).toBe(true);
		});
	});

	describe("disabled prop", () => {
		it("hides the add buttons and options", () => {
			const wrapper = field({ disabled: true });
			expect(wrapper.findAllComponents(Button)).toHaveLength(0);
		});
	});

	describe("fieldsets prop", () => {
		it("offers to add layouts with fieldsets", () => {
			const wrapper = field();
			expect(wrapper.find(".options").findAllComponents(Button)).toHaveLength(
				2
			);
			expect(wrapper.find("footer").exists()).toBe(true);
		});

		it("offers nothing without fieldsets", () => {
			const wrapper = field({ fieldsets: {} });
			expect(wrapper.findAllComponents(Button)).toHaveLength(0);
		});
	});

	describe("max prop", () => {
		it("passes the limits to the k-validator", () => {
			const wrapper = mount({ max: 3, min: 1, required: true });
			expect(wrapper.find("k-validator").attributes()).toMatchObject({
				count: "1",
				max: "3",
				min: "1",
				required: "true"
			});
		});
	});

	describe("value prop", () => {
		it("passes the layouts to the k-layouts", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Layouts).props("value")).toStrictEqual(
				layouts
			);
		});
	});

	// computed
	describe("options computed", () => {
		it("offers to copy, paste and delete all", () => {
			const wrapper = mount();
			const icons = wrapper.vm.options.map(
				(option: string | { icon: string }) =>
					typeof option === "string" ? option : option.icon
			);
			expect(icons).toStrictEqual(["template", "download", "-", "trash"]);
		});

		it("disables copying and deleting without layouts", () => {
			const wrapper = mount({ value: [] });
			expect(wrapper.vm.options[0]).toMatchObject({ disabled: true });
			expect(wrapper.vm.options[3]).toMatchObject({ disabled: true });
		});

		it("runs the option on the k-layouts", () => {
			const wrapper = mount();
			for (const option of wrapper.vm.options) {
				if (typeof option !== "string") {
					option.click();
				}
			}
			expect(Layouts.methods.copy).toHaveBeenCalledOnce();
			expect(Layouts.methods.pasteboard).toHaveBeenCalledOnce();
			expect(Layouts.methods.removeAll).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("click event", () => {
		it("adds a layout at the start with the header button", async () => {
			const wrapper = field();
			const [add] = wrapper.find(".options").findAllComponents(Button);
			await add.trigger("click");
			expect(Layouts.methods.select).toHaveBeenCalledWith(0);
		});

		it("adds a layout at the end with the footer button", async () => {
			const wrapper = field();
			await wrapper.find("footer").findComponent(Button).trigger("click");
			expect(Layouts.methods.select).toHaveBeenCalledWith(1);
		});

		it("toggles the options", async () => {
			const wrapper = field();
			const [, dots] = wrapper.find(".options").findAllComponents(Button);
			await dots.trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});
	});

	describe("input event", () => {
		it("emits the layouts of the k-layouts", () => {
			const wrapper = mount();
			wrapper.findComponent(Layouts).vm.$emit("input", []);
			expect(wrapper.emitted("input")).toStrictEqual([[[]]]);
		});
	});
});
