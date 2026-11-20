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
		it.rendersAs(mount, "DIV", "k-toolbar");
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

const FocusableButton = {
	name: "k-button",
	props: ["current", "disabled", "icon", "title"],
	methods: {
		focus(this: { $el: HTMLElement }) {
			this.$el.focus();
		}
	},
	template: `<button :aria-disabled="disabled ? 'true' : null" :title="title" />`
};

function mountFocusable(buttons: unknown[], dir = "ltr") {
	const root = document.createElement("div");
	root.dir = dir;
	document.body.append(root);
	return vueMount(Toolbar, {
		props: { buttons },
		attachTo: root,
		global: {
			stubs: {
				"k-button": FocusableButton,
				"k-dropdown": true
			}
		}
	});
}

const focusButtons = [
	{ title: "a" },
	"|",
	{ title: "b", disabled: true },
	{ title: "c", when: false },
	{ title: "d" },
	{ title: "e" }
];

function tabindexes(wrapper: ReturnType<typeof mountFocusable>) {
	return Object.fromEntries(
		wrapper
			.findAll(".k-toolbar-button")
			.map((button: ReturnType<typeof wrapper.find>) => [
				button.attributes("title"),
				button.attributes("tabindex")
			])
	);
}

describe("k-toolbar keyboard", () => {
	describe("tab stop", () => {
		it("makes only the first button reachable with Tab", () => {
			const wrapper = mountFocusable(focusButtons);
			expect(tabindexes(wrapper)).toStrictEqual({
				a: "0",
				b: "-1",
				d: "-1",
				e: "-1"
			});
			wrapper.unmount();
		});

		it("keeps a disabled first button reachable", () => {
			const wrapper = mountFocusable([
				{ title: "a", disabled: true },
				{ title: "b" }
			]);
			expect(tabindexes(wrapper)).toStrictEqual({ a: "0", b: "-1" });
			wrapper.unmount();
		});

		it("remembers the last focused button", async () => {
			const wrapper = mountFocusable(focusButtons);
			await wrapper.find("[title=d]").trigger("focus");
			expect(tabindexes(wrapper).d).toBe("0");
			expect(tabindexes(wrapper).a).toBe("-1");
			wrapper.unmount();
		});
	});

	describe("arrow keys", () => {
		it("moves to the next button, disabled or not", async () => {
			const wrapper = mountFocusable(focusButtons);
			(wrapper.find("[title=a]").element as HTMLElement).focus();
			await wrapper.find("[title=a]").trigger("keydown", { key: "ArrowRight" });
			expect((document.activeElement as HTMLElement).title).toBe("b");
			wrapper.unmount();
		});

		it("moves to the previous button", async () => {
			const wrapper = mountFocusable(focusButtons);
			(wrapper.find("[title=e]").element as HTMLElement).focus();
			await wrapper.find("[title=e]").trigger("keydown", { key: "ArrowLeft" });
			expect((document.activeElement as HTMLElement).title).toBe("d");
			wrapper.unmount();
		});

		it("swaps the arrow keys in right-to-left languages", async () => {
			const wrapper = mountFocusable(focusButtons, "rtl");
			(wrapper.find("[title=d]").element as HTMLElement).focus();
			await wrapper.find("[title=d]").trigger("keydown", { key: "ArrowRight" });
			expect((document.activeElement as HTMLElement).title).toBe("b");
			wrapper.unmount();
		});

		it("stays on the last button", async () => {
			const wrapper = mountFocusable(focusButtons);
			(wrapper.find("[title=e]").element as HTMLElement).focus();
			await wrapper.find("[title=e]").trigger("keydown", { key: "ArrowRight" });
			expect((document.activeElement as HTMLElement).title).toBe("e");
			wrapper.unmount();
		});

		it("leaves keys a button handles itself", async () => {
			const key = vi.fn((event) => event.preventDefault());
			const wrapper = mountFocusable([{ title: "a", key }, { title: "b" }]);
			(wrapper.find("[title=a]").element as HTMLElement).focus();
			await wrapper.find("[title=a]").trigger("keydown", { key: "ArrowRight" });
			expect(key).toHaveBeenCalled();
			expect((document.activeElement as HTMLElement).title).toBe("a");
			wrapper.unmount();
		});
	});
});
