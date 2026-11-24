import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import DropdownItem from "./DropdownItem.vue";

const Button = {
	name: "k-button",
	props: ["current", "dialog", "disabled", "drawer", "icon", "link", "text"],
	emits: ["click"],
	methods: { focus: vi.fn(), tab: vi.fn() },
	template: `<button @click="$emit('click', $event)"><slot /></button>`
};

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(DropdownItem, {
		props,
		attrs,
		slots,
		global: { stubs: { "k-button": Button } }
	});
}

function button(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Button);
}

describe("k-dropdown-item", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "BUTTON", "k-dropdown-item");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("button props", () => {
		it("passes them on to the button", () => {
			const wrapper = mount({
				current: true,
				dialog: "pages/a/changeTitle",
				disabled: true,
				icon: "edit",
				link: "/pages/a",
				text: "Rename"
			});
			expect(button(wrapper).props()).toStrictEqual({
				current: true,
				dialog: "pages/a/changeTitle",
				disabled: true,
				drawer: undefined,
				icon: "edit",
				link: "/pages/a",
				text: "Rename"
			});
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the button", () => {
			const wrapper = mount();
			const focus = vi.spyOn(button(wrapper).vm, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	describe("tab()", () => {
		it("tabs to the button", () => {
			const wrapper = mount();
			const tab = vi.spyOn(button(wrapper).vm, "tab");
			wrapper.vm.tab();
			expect(tab).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("events", () => {
		it("emits click", async () => {
			const wrapper = mount();
			await button(wrapper).trigger("click");
			expect(wrapper.emitted("click")).toHaveLength(1);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the content", () => {
			const wrapper = mount({}, {}, { default: "Rename" });
			expect(wrapper.text()).toBe("Rename");
		});
	});
});
