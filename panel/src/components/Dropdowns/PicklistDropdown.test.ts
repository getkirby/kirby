import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import PicklistDropdown from "./PicklistDropdown.vue";

const Dropdown = {
	name: "k-dropdown",
	props: ["alignX", "disabled", "navigate"],
	methods: { close: vi.fn(), open: vi.fn(), toggle: vi.fn() },
	template: "<div><slot /></div>"
};
const PicklistInput = {
	name: "k-picklist-input",
	props: ["max", "options", "search", "value"],
	emits: ["create", "escape", "input"],
	template: "<nav />"
};

const options = [
	{ text: "A", value: "a" },
	{ text: "B", value: "b" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(PicklistDropdown, {
		props: { options, ...props },
		attrs,
		global: {
			stubs: { "k-dropdown": Dropdown, "k-picklist-input": PicklistInput }
		}
	});
}

function dropdown(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Dropdown);
}

function picklist(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(PicklistInput);
}

describe("k-picklist-dropdown", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-picklist-dropdown");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders a dropdown without keyboard navigation", () => {
			expect(dropdown(mount()).props()).toStrictEqual({
				alignX: "start",
				disabled: false,
				navigate: false
			});
		});

		it("renders the picklist in it", () => {
			expect(picklist(mount({ max: 2, value: ["a"] })).props()).toMatchObject({
				max: 2,
				options,
				value: ["a"]
			});
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the dropdown", () => {
			expect(dropdown(mount({ disabled: true })).props("disabled")).toBe(true);
		});
	});

	// methods
	describe("close()", () => {
		it("closes the dropdown", () => {
			const wrapper = mount();
			const close = vi.spyOn(dropdown(wrapper).vm, "close");
			wrapper.vm.close();
			expect(close).toHaveBeenCalledOnce();
		});
	});

	describe("open()", () => {
		it("opens the dropdown at the opener", () => {
			const wrapper = mount();
			const open = vi.spyOn(dropdown(wrapper).vm, "open");
			const opener = document.createElement("button");
			wrapper.vm.open(opener);
			expect(open).toHaveBeenCalledWith(opener);
		});
	});

	describe("toggle()", () => {
		it("toggles the dropdown", () => {
			const wrapper = mount();
			const toggle = vi.spyOn(dropdown(wrapper).vm, "toggle");
			wrapper.vm.toggle();
			expect(toggle).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("events", () => {
		it("emits the picked values", () => {
			const wrapper = mount();
			picklist(wrapper).vm.$emit("input", ["b"]);
			expect(wrapper.emitted("input")).toStrictEqual([[["b"]]]);
		});

		it("emits a new value to create", () => {
			const wrapper = mount();
			picklist(wrapper).vm.$emit("create", "c");
			expect(wrapper.emitted("create")).toStrictEqual([["c"]]);
		});

		it("closes on escape", () => {
			const wrapper = mount();
			const close = vi.spyOn(dropdown(wrapper).vm, "close");
			picklist(wrapper).vm.$emit("escape");
			expect(close).toHaveBeenCalledOnce();
		});

		it("keeps clicks in the dropdown from the page", async () => {
			const onClick = vi.fn();
			const wrapper = vueMount(PicklistDropdown, {
				props: { options },
				attachTo: document.body,
				global: {
					stubs: { "k-dropdown": Dropdown, "k-picklist-input": PicklistInput }
				}
			});
			document.body.addEventListener("click", onClick);
			await picklist(wrapper).trigger("click");
			await dropdown(wrapper).trigger("click");
			document.body.removeEventListener("click", onClick);
			expect(onClick).not.toHaveBeenCalled();
		});
	});
});
