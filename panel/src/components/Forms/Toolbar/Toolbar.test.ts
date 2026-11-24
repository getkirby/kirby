import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Toolbar from "./Toolbar.vue";

const Dropdown = {
	name: "k-dropdown",
	props: ["options", "theme"],
	methods: { close: vi.fn(), toggle: vi.fn() },
	template: "<div />"
};
const bold = { click: vi.fn(), icon: "bold", label: "Bold" };

function mount(props = {}, attrs = {}) {
	return vueMount(Toolbar, {
		props: { buttons: [bold], ...props },
		attrs,
		global: {
			stubs: { "k-dropdown": Dropdown }
		}
	});
}

describe("k-toolbar", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "NAV", "k-toolbar");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("buttons prop", () => {
		it("renders a k-button for each button", () => {
			const wrapper = mount({
				buttons: [
					bold,
					{
						class: "is-wide",
						current: true,
						disabled: true,
						icon: "code",
						title: "Code"
					}
				]
			});
			const buttons = wrapper.findAll("k-button");
			expect(buttons[0].attributes("icon")).toBe("bold");
			expect(buttons[0].attributes("title")).toBe("Bold");
			expect(buttons[0].attributes("tabindex")).toBe("0");
			expect(buttons[1].attributes("title")).toBe("Code");
			expect(buttons[1].attributes("current")).toBe("true");
			expect(buttons[1].attributes("disabled")).toBe("true");
			expect(buttons[1].classes()).toStrictEqual([
				"k-toolbar-button",
				"is-wide"
			]);
		});

		it("renders a separator", () => {
			const wrapper = mount({ buttons: [bold, "|", bold] });
			const tags = wrapper
				.findAll(":scope > *")
				.map((el) => el.element.tagName);
			expect(tags).toStrictEqual(["K-BUTTON", "HR", "K-BUTTON"]);
		});

		it("hides a button when it should not show", () => {
			const wrapper = mount({ buttons: [bold, { ...bold, when: false }] });
			expect(wrapper.findAll("k-button")).toHaveLength(1);
		});

		it("renders a k-dropdown for a button with dropdown", () => {
			const dropdown = [{ label: "H1" }];
			const wrapper = mount({ buttons: [{ ...bold, dropdown }] });
			expect(wrapper.findComponent(Dropdown).props("options")).toStrictEqual(
				dropdown
			);
		});

		it("renders nothing without buttons", () => {
			const wrapper = mount({ buttons: [] });
			expect(wrapper.find("nav").exists()).toBe(false);
		});
	});

	describe("theme prop", () => {
		it("defaults to light with dark dropdowns", () => {
			const wrapper = mount({ buttons: [{ ...bold, dropdown: [{}] }] });
			expect(wrapper.attributes("data-theme")).toBe("light");
			expect(wrapper.findComponent(Dropdown).props("theme")).toBe("dark");
		});

		it("uses light dropdowns in a dark toolbar", () => {
			const wrapper = mount({
				buttons: [{ ...bold, dropdown: [{}] }],
				theme: "dark"
			});
			expect(wrapper.findComponent(Dropdown).props("theme")).toBe("light");
		});
	});

	// methods
	describe("close()", () => {
		it("closes all dropdowns", () => {
			const wrapper = mount({
				buttons: [
					{ ...bold, dropdown: [{}] },
					{ ...bold, dropdown: [{}] }
				]
			});
			wrapper.vm.close();
			expect(Dropdown.methods.close).toHaveBeenCalledTimes(2);
		});
	});

	// events
	describe("click event", () => {
		it("calls the click handler of the button", async () => {
			const wrapper = mount();
			await wrapper.find("k-button").trigger("click");
			expect(bold.click).toHaveBeenCalledOnce();
		});

		it("toggles the dropdown of the button", async () => {
			const wrapper = mount({ buttons: [{ ...bold, dropdown: [{}] }] });
			await wrapper.find("k-button").trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
			expect(bold.click).not.toHaveBeenCalled();
		});
	});

	describe("keydown event", () => {
		it("calls the key handler of the button", async () => {
			const key = vi.fn();
			const wrapper = mount({ buttons: [{ ...bold, key }] });
			await wrapper.find("k-button").trigger("keydown", { key: "ArrowDown" });
			expect(key).toHaveBeenCalledOnce();
		});
	});
});
