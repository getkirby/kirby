import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import BlockBackgroundDropdown from "./BlockBackgroundDropdown.vue";

const Button = {
	name: "k-button",
	props: ["dropdown", "size", "variant"],
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

function mount(props = {}, attrs = {}) {
	return vueMount(BlockBackgroundDropdown, {
		props: { value: "var(--pattern)", ...props },
		attrs,
		global: {
			stubs: { "k-button": Button, "k-dropdown": Dropdown }
		}
	});
}

describe("k-block-background-dropdown", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-background-dropdown");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders a small dropdown button", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Button).props()).toStrictEqual({
				dropdown: true,
				size: "xs",
				variant: "filled"
			});
		});

		it("offers the backgrounds", () => {
			const wrapper = mount();
			const dropdown = wrapper.findComponent(Dropdown);
			expect(dropdown.props("alignX")).toBe("end");
			expect(dropdown.props("options")).toStrictEqual([
				{
					click: "var(--block-color-back)",
					text: "field.blocks.figure.back.plain"
				},
				{
					click: "var(--pattern-light)",
					text: "field.blocks.figure.back.pattern.light"
				},
				{
					click: "var(--pattern)",
					text: "field.blocks.figure.back.pattern.dark"
				}
			]);
		});
	});

	// props
	describe("value prop", () => {
		it("shows the background", () => {
			const frame = mount().find("k-color-frame");
			expect(frame.attributes("color")).toBe("var(--pattern)");
			expect(frame.attributes("ratio")).toBe("1/1");
		});

		it("shows transparent as the plain background", () => {
			const wrapper = mount({ value: "transparent" });
			expect(wrapper.find("k-color-frame").attributes("color")).toBe(
				"var(--block-color-back)"
			);
		});
	});

	// events
	describe("events", () => {
		it("toggles the dropdown on click", async () => {
			const wrapper = mount();
			const toggle = vi.spyOn(wrapper.findComponent(Dropdown).vm, "toggle");
			await wrapper.findComponent(Button).trigger("click");
			expect(toggle).toHaveBeenCalledOnce();
		});

		it("emits the selected background", () => {
			const wrapper = mount();
			wrapper
				.findComponent(Dropdown)
				.vm.$emit("action", "var(--pattern-light)");
			expect(wrapper.emitted("input")).toStrictEqual([
				["var(--pattern-light)"]
			]);
		});
	});
});
