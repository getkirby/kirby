import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import OptionsDropdown from "./OptionsDropdown.vue";

const Button = {
	name: "k-button",
	props: ["disabled", "dropdown", "icon", "size", "text", "title", "variant"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)"><slot /></button>`
};
const Dropdown = {
	name: "k-dropdown",
	props: ["alignX", "options"],
	emits: ["action"],
	methods: { toggle: vi.fn() },
	template: "<div />"
};

const options = [
	{ click: "edit", icon: "edit", text: "Edit" },
	{ click: "remove", icon: "trash", text: "Remove" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(OptionsDropdown, {
		props: { options, ...props },
		attrs,
		global: { stubs: { "k-button": Button, "k-dropdown": Dropdown } }
	});
}

function toggle(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Button);
}

describe("k-options-dropdown", () => {
	// $el
	describe("element", () => {
		it("renders a toggle for the options", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-options-dropdown").exists()).toBe(true);
			expect(toggle(wrapper).classes()).toContain("k-options-dropdown-toggle");
			expect(toggle(wrapper).props()).toStrictEqual({
				disabled: false,
				dropdown: true,
				icon: "dots",
				size: undefined,
				text: null,
				title: "options",
				variant: undefined
			});
		});

		it("passes the attributes to the element", () => {
			const wrapper = mount({}, { "data-id": "a" });
			expect(wrapper.find(".k-options-dropdown").attributes("data-id")).toBe(
				"a"
			);
		});
	});

	// props
	describe("align prop", () => {
		it("aligns the options to the right by default", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Dropdown).props()).toStrictEqual({
				alignX: "right",
				options
			});
		});

		it("aligns the options", () => {
			const wrapper = mount({ align: "left" });
			expect(wrapper.findComponent(Dropdown).props("alignX")).toBe("left");
		});
	});

	describe("disabled prop", () => {
		it("disables the toggle", () => {
			expect(toggle(mount({ disabled: true })).props("disabled")).toBe(true);
		});
	});

	describe("icon prop", () => {
		it("sets the icon of the toggle", () => {
			expect(toggle(mount({ icon: "cog" })).props("icon")).toBe("cog");
		});
	});

	describe("options prop", () => {
		it("renders nothing without options", () => {
			const wrapper = mount({ options: [] });
			expect(wrapper.findComponent(Button).exists()).toBe(false);
			expect(wrapper.find(".k-options-dropdown").exists()).toBe(false);
		});

		it("renders a single option as button", () => {
			const wrapper = mount({
				options: [
					{
						click: "remove",
						icon: "trash",
						size: "xs",
						text: "Remove",
						tooltip: "Remove it",
						variant: "filled"
					}
				]
			});
			expect(wrapper.findComponent(Dropdown).exists()).toBe(false);
			expect(toggle(wrapper).text()).toBe("Remove");
			expect(toggle(wrapper).props()).toMatchObject({
				icon: "trash",
				size: "xs",
				title: "Remove it",
				variant: "filled"
			});
		});

		it("falls back to the toggle settings for a single option", () => {
			const wrapper = mount({
				icon: "cog",
				options: [{ click: "remove", text: "Remove" }],
				size: "sm",
				variant: "dimmed"
			});
			expect(toggle(wrapper).props()).toMatchObject({
				icon: "cog",
				size: "sm",
				title: "Remove",
				variant: "dimmed"
			});
		});

		it("lets a dropdown load function options", () => {
			const load = (ready: (options: unknown[]) => void) => ready(options);
			const wrapper = mount({ options: load });
			expect(wrapper.findComponent(Dropdown).props("options")).toBe(load);
		});
	});

	describe("size prop", () => {
		it("sets the size of the toggle", () => {
			expect(toggle(mount({ size: "xs" })).props("size")).toBe("xs");
		});
	});

	describe("text prop", () => {
		it("shows a text on the toggle", () => {
			expect(toggle(mount({ text: "More" })).props("text")).toBe("More");
		});

		it("shows no text on the toggle when false", () => {
			expect(toggle(mount({ text: false })).props("text")).toBe(null);
		});

		it("replaces the text of a single option", () => {
			const wrapper = mount({ options: [options[0]], text: "Change" });
			expect(toggle(wrapper).text()).toBe("Change");
		});

		it("hides the text of a single option", () => {
			const wrapper = mount({ options: [options[0]], text: false });
			expect(toggle(wrapper).text()).toBe("");
		});
	});

	describe("variant prop", () => {
		it("sets the variant of the toggle", () => {
			expect(toggle(mount({ variant: "filled" })).props("variant")).toBe(
				"filled"
			);
		});
	});

	// methods
	describe("onAction()", () => {
		it("emits a string as action and option", () => {
			const wrapper = mount();
			wrapper.vm.onAction("remove", options[1], 1);
			expect(wrapper.emitted("action")).toStrictEqual([
				["remove", options[1], 1]
			]);
			expect(wrapper.emitted("option")).toStrictEqual([
				["remove", options[1], 1]
			]);
		});

		it("calls a function", () => {
			const wrapper = mount();
			const click = vi.fn();
			wrapper.vm.onAction(click, options[0], 0);
			expect(click).toHaveBeenCalledOnce();
			expect(wrapper.emitted("action")).toBeUndefined();
		});
	});

	// events
	describe("events", () => {
		it("toggles the options", async () => {
			const wrapper = mount();
			const toggleOptions = vi.spyOn(
				wrapper.findComponent(Dropdown).vm,
				"toggle"
			);
			await toggle(wrapper).trigger("click");
			expect(toggleOptions).toHaveBeenCalledOnce();
		});

		it("emits the action of an option", () => {
			const wrapper = mount();
			wrapper.findComponent(Dropdown).vm.$emit("action", "edit", options[0], 0);
			expect(wrapper.emitted("option")).toStrictEqual([
				["edit", options[0], 0]
			]);
		});

		it("emits the action of a single option", async () => {
			const option = { click: "remove", option: "delete", text: "Remove" };
			const wrapper = mount({ options: [option] });
			await toggle(wrapper).trigger("click");
			expect(wrapper.emitted("option")).toStrictEqual([["delete", option, 0]]);
		});
	});
});
