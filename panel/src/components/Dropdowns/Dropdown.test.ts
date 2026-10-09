import { afterEach, describe, expect, it, vi } from "@test/unit";
import {
	enableAutoUnmount,
	flushPromises,
	mount as vueMount,
	type ComponentMountingOptions,
	type VueWrapper
} from "@vue/test-utils";
import { h } from "vue";
import Navigate from "@/components/Navigation/Navigate.vue";
import Dropdown from "./Dropdown.vue";

type Options = {
	mocks?: Record<string, unknown>;
	slots?: ComponentMountingOptions<typeof Dropdown>["slots"];
};

/**
 * Builds a 40×20 button at 100/50 to open the dropdown from
 */
function button(rect: Partial<DOMRect> = {}) {
	const button = document.createElement("button");
	button.getBoundingClientRect = () =>
		({ height: 20, left: 100, top: 50, width: 40, ...rect }) as DOMRect;
	return button;
}

/**
 * Mounts the dropdown with its content rendered,
 * but without showing the dialog yet
 */
function mount(
	props = {},
	attrs = {},
	{ mocks = {}, slots = {} }: Options = {}
) {
	return vueMount(Dropdown, {
		props,
		attrs,
		slots,
		attachTo: document.body,
		data: () => ({ isOpen: true }),
		global: {
			components: { "k-navigate": Navigate },
			mocks: { $panel: { direction: "ltr" }, ...mocks }
		}
	});
}

/**
 * Opens the dropdown from the given opener
 */
async function open(wrapper = mount(), opener: unknown = button()) {
	wrapper.vm.open(opener);
	await flushPromises();
	return wrapper;
}

describe("Dropdown.vue", () => {
	enableAutoUnmount(afterEach);

	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIALOG", "k-dropdown");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders nothing while closed", () => {
			expect(vueMount(Dropdown).html()).toBe("<!--v-if-->");
		});
	});

	// props
	describe("alignX prop", () => {
		it("defaults to start", () => {
			expect(mount().attributes("data-align-x")).toBe("start");
		});

		it("renders as data-align-x attribute", () => {
			const wrapper = mount({ alignX: "end" });
			expect(wrapper.attributes("data-align-x")).toBe("end");
		});
	});

	describe("alignY prop", () => {
		it("defaults to bottom", () => {
			expect(mount().attributes("data-align-y")).toBe("bottom");
		});

		it("renders as data-align-y attribute", () => {
			const wrapper = mount({ alignY: "top" });
			expect(wrapper.attributes("data-align-y")).toBe("top");
		});
	});

	describe("disabled prop", () => {
		it("prevents opening the dropdown", async () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.vm.open(button())).toBe(false);

			await flushPromises();
			expect(wrapper.element.open).toBe(false);
			expect(wrapper.emitted("open")).toBeUndefined();
		});
	});

	describe("navigate prop", () => {
		const slots = { default: "<button>A</button><button>B</button>" };

		it("moves through the items with the up and down keys", async () => {
			const wrapper = mount({}, {}, { slots });
			const [a, b] = wrapper.findAll("button");

			a.element.focus();
			await a.trigger("keydown", { key: "ArrowDown" });
			expect(document.activeElement).toBe(b.element);

			await b.trigger("keydown", { key: "ArrowUp" });
			expect(document.activeElement).toBe(a.element);
		});

		it("ignores the left and right keys", async () => {
			const wrapper = mount({}, {}, { slots });
			const [a] = wrapper.findAll("button");

			a.element.focus();
			await a.trigger("keydown", { key: "ArrowRight" });
			expect(document.activeElement).toBe(a.element);
		});

		it("turns off the keyboard navigation when false", async () => {
			const wrapper = mount({ navigate: false }, {}, { slots });
			const [a] = wrapper.findAll("button");

			a.element.focus();
			await a.trigger("keydown", { key: "ArrowDown" });
			expect(document.activeElement).toBe(a.element);
		});
	});

	describe("options prop", () => {
		function items(wrapper: VueWrapper) {
			return wrapper.findAll("k-dropdown-item").map((item) => item.text());
		}

		it("renders an item for each option", async () => {
			const options = [{ text: "Edit" }, { text: "Delete" }];
			const wrapper = await open(mount({ options }));
			expect(items(wrapper)).toStrictEqual(["Edit", "Delete"]);
		});

		it("renders a separator for -", async () => {
			const options = [{ text: "Edit" }, "-", { text: "Delete" }];
			const wrapper = await open(mount({ options }));
			expect(wrapper.findAll("hr")).toHaveLength(1);
			expect(items(wrapper)).toStrictEqual(["Edit", "Delete"]);
		});

		it("prefers the label over the text", async () => {
			const options = [{ label: "Label", text: "Text" }];
			const wrapper = await open(mount({ options }));
			expect(items(wrapper)).toStrictEqual(["Label"]);
		});

		it("skips options whose when is false", async () => {
			const options = [{ text: "Edit", when: false }, { text: "Delete" }];
			const wrapper = await open(mount({ options }));
			expect(items(wrapper)).toStrictEqual(["Delete"]);
		});
	});

	describe("theme prop", () => {
		it("defaults to dark", () => {
			expect(mount().attributes("data-theme")).toBe("dark");
		});

		it("renders as data-theme attribute", () => {
			const wrapper = mount({ theme: "light" });
			expect(wrapper.attributes("data-theme")).toBe("light");
		});
	});

	// methods
	describe("close()", () => {
		it("closes the open dropdown", async () => {
			const wrapper = await open();
			wrapper.vm.close();
			expect(wrapper.vm.isOpen).toBe(false);
		});

		it("does nothing while closed", () => {
			expect(() => vueMount(Dropdown).vm.close()).not.toThrow();
		});
	});

	describe("fetchOptions()", () => {
		it("passes an options array on", async () => {
			const ready = vi.fn();
			const options = [{ text: "Edit" }];
			await mount({ options }).vm.fetchOptions(ready);
			expect(ready).toHaveBeenCalledWith(options);
		});

		it("lets an options callback resolve the options", async () => {
			const ready = vi.fn();
			const options = vi.fn((ready) => ready([{ text: "Edit" }]));
			await mount({ options }).vm.fetchOptions(ready);
			expect(options).toHaveBeenCalledWith(ready);
			expect(ready).toHaveBeenCalledWith([{ text: "Edit" }]);
		});

		it("loads the options from a dropdown URL", async () => {
			const ready = vi.fn();
			const $dropdown = vi.fn(
				() => (ready: (items: unknown[]) => void) => ready([{ text: "Edit" }])
			);
			const wrapper = mount(
				{ options: "pages/test" },
				{},
				{ mocks: { $dropdown } }
			);

			await wrapper.vm.fetchOptions(ready);
			expect($dropdown).toHaveBeenCalledWith("pages/test");
			expect(ready).toHaveBeenCalledWith([{ text: "Edit" }]);
		});

		it("passes the current items on without options", async () => {
			const ready = vi.fn();
			await mount().vm.fetchOptions(ready);
			expect(ready).toHaveBeenCalledWith([]);
		});
	});

	describe("focus()", () => {
		const slots = { default: "<button>A</button><button>B</button>" };

		it("focuses the first item by default", () => {
			const wrapper = mount({}, {}, { slots });
			wrapper.vm.focus();
			expect(document.activeElement).toBe(wrapper.find("button").element);
		});

		it("focuses the item at the given index", () => {
			const wrapper = mount({}, {}, { slots });
			wrapper.vm.focus(1);
			expect(document.activeElement).toBe(wrapper.findAll("button")[1].element);
		});
	});

	describe("onOptionClick()", () => {
		it("closes the dropdown", async () => {
			const wrapper = await open();
			wrapper.vm.onOptionClick({});
			expect(wrapper.vm.isOpen).toBe(false);
		});

		it("calls a click callback on the dropdown", () => {
			const click = vi.fn();
			const wrapper = mount();
			wrapper.vm.onOptionClick({ click });
			expect(click.mock.contexts[0]).toBe(wrapper.vm);
		});

		it("emits a click string as action", () => {
			const wrapper = mount();
			wrapper.vm.onOptionClick({ click: "edit" });
			expect(wrapper.emitted("action")).toStrictEqual([["edit"]]);
		});

		it("emits a click object by its name", () => {
			const wrapper = mount();
			wrapper.vm.onOptionClick({ click: { name: "action", payload: "edit" } });
			expect(wrapper.emitted("action")).toStrictEqual([["edit"]]);
		});

		it("emits a global click object on the event bus", () => {
			const $events = { emit: vi.fn() };
			const wrapper = mount({}, {}, { mocks: { $events } });
			wrapper.vm.onOptionClick({ click: { global: "edit", payload: 1 } });
			expect($events.emit).toHaveBeenCalledWith("edit", 1);
		});
	});

	describe("open()", () => {
		it("shows the dialog as modal", async () => {
			const wrapper = await open();
			expect(wrapper.element.open).toBe(true);
		});

		it("closes any other open dropdown", async () => {
			const a = await open();
			const b = await open();
			expect(a.vm.isOpen).toBe(false);
			expect(b.vm.isOpen).toBe(true);
		});

		it("falls back to the button of the current event", async () => {
			const opener = button();
			const icon = document.createElement("span");
			opener.append(icon);

			const wrapper = mount();
			Object.defineProperty(window, "event", {
				configurable: true,
				value: { target: icon }
			});
			wrapper.vm.open();
			Reflect.deleteProperty(window, "event");

			await flushPromises();
			expect(wrapper.vm.opener).toBe(opener);
		});
	});

	describe("resetPosition()", () => {
		it("moves the dropdown back to the origin", () => {
			const wrapper = mount();
			wrapper.vm.position = { x: 10, y: 20 };
			wrapper.vm.resetPosition();
			expect(wrapper.vm.position).toStrictEqual({ x: 0, y: 0 });
		});
	});

	describe("setPosition()", () => {
		// opens the dropdown with its own size from a button at 100/50
		async function position(props = {}, opener = {}, rect = {}) {
			const wrapper = mount(props);
			wrapper.element.getBoundingClientRect = () =>
				({ height: 0, left: 0, top: 0, width: 0, ...rect }) as DOMRect;
			return open(wrapper, button(opener));
		}

		it("places the dropdown below the start of the opener", async () => {
			const wrapper = await position();
			expect(wrapper.attributes("data-align-x")).toBe("start");
			expect(wrapper.attributes("data-align-y")).toBe("bottom");
			expect(wrapper.element.style.left).toBe("100px");
			expect(wrapper.element.style.top).toBe("70px");
		});

		it("aligns the dropdown with the end of the opener", async () => {
			const wrapper = await position({ alignX: "end" });
			expect(wrapper.attributes("data-align-x")).toBe("end");
			expect(wrapper.element.style.left).toBe("140px");
		});

		it("flips to the start without space on the left", async () => {
			const wrapper = await position(
				{ alignX: "end" },
				{ left: 50 },
				{ width: 100 }
			);
			expect(wrapper.attributes("data-align-x")).toBe("start");
			expect(wrapper.element.style.left).toBe("50px");
		});

		it("flips to the end without space on the right", async () => {
			const wrapper = await position(
				{},
				{ left: 950 },
				{ left: 950, width: 100 }
			);
			expect(wrapper.attributes("data-align-x")).toBe("end");
			expect(wrapper.element.style.left).toBe("990px");
		});

		it("maps left and right to start and end", async () => {
			const left = await position({ alignX: "left" });
			expect(left.attributes("data-align-x")).toBe("start");

			const right = await position({ alignX: "right" });
			expect(right.attributes("data-align-x")).toBe("end");
		});

		it("mirrors the horizontal alignment for right-to-left languages", async () => {
			const $panel = { direction: "rtl" };
			const wrapper = await open(mount({}, {}, { mocks: { $panel } }));
			expect(wrapper.attributes("data-align-x")).toBe("end");
			expect(wrapper.element.style.left).toBe("140px");
		});

		it("places the dropdown above the opener", async () => {
			const wrapper = await position(
				{ alignY: "top" },
				{},
				{ height: 30, top: 70 }
			);
			expect(wrapper.attributes("data-align-y")).toBe("top");
			expect(wrapper.element.style.top).toBe("50px");
		});

		it("flips to the bottom without space above", async () => {
			const wrapper = await position(
				{ alignY: "top" },
				{},
				{ height: 100, top: 70 }
			);
			expect(wrapper.attributes("data-align-y")).toBe("bottom");
			expect(wrapper.element.style.top).toBe("70px");
		});

		it("flips to the top without space below", async () => {
			const wrapper = await position(
				{},
				{ top: 700 },
				{ height: 100, top: 720 }
			);
			expect(wrapper.attributes("data-align-y")).toBe("top");
			expect(wrapper.element.style.top).toBe("700px");
		});

		it("measures the element of a component opener", async () => {
			const wrapper = await open(mount(), { $el: button() });
			expect(wrapper.element.style.left).toBe("100px");
			expect(wrapper.element.style.top).toBe("70px");
		});
	});

	describe("toggle()", () => {
		it("opens a closed dropdown", async () => {
			const wrapper = mount();
			wrapper.vm.isOpen = false;
			wrapper.vm.toggle(button());

			await flushPromises();
			expect(wrapper.vm.isOpen).toBe(true);
			expect(wrapper.element.open).toBe(true);
		});

		it("closes an open dropdown", async () => {
			const wrapper = await open();
			wrapper.vm.toggle();
			expect(wrapper.vm.isOpen).toBe(false);
		});
	});

	// events
	describe("click event", () => {
		it("closes the dropdown", async () => {
			const wrapper = await open();
			await wrapper.trigger("click");
			expect(wrapper.vm.isOpen).toBe(false);
		});
	});

	describe("close event", () => {
		it("is emitted when the dialog closes", async () => {
			const wrapper = await open();
			wrapper.element.close();
			expect(wrapper.emitted("close")).toHaveLength(1);
		});

		it("moves the dropdown back to the origin", async () => {
			const wrapper = await open();
			wrapper.element.close();
			expect(wrapper.vm.position).toStrictEqual({ x: 0, y: 0 });
		});
	});

	describe("open event", () => {
		it("is emitted once the dropdown is positioned", async () => {
			const wrapper = await open();
			expect(wrapper.emitted("open")).toHaveLength(1);
		});
	});

	describe("resize event", () => {
		it("repositions the open dropdown", async () => {
			const opener = button();
			const wrapper = await open(mount(), opener);

			opener.getBoundingClientRect = () =>
				({ height: 20, left: 200, top: 50, width: 40 }) as DOMRect;
			window.dispatchEvent(new Event("resize"));

			await flushPromises();
			expect(wrapper.element.style.left).toBe("200px");
		});

		it("is ignored once the dropdown is closed", async () => {
			const wrapper = await open();
			wrapper.vm.close();

			window.dispatchEvent(new Event("resize"));

			await flushPromises();
			expect(wrapper.vm.position).toStrictEqual({ x: 0, y: 0 });
		});
	});

	// slots
	describe("default slot", () => {
		it("replaces the option items", async () => {
			const options = [{ text: "Edit" }];
			const slots = { default: "<button>Custom</button>" };
			const wrapper = await open(mount({ options }, {}, { slots }));
			expect(wrapper.find("k-dropdown-item").exists()).toBe(false);
			expect(wrapper.find("button").text()).toBe("Custom");
		});

		it("receives the items", async () => {
			const options = [{ text: "Edit" }, { text: "Delete" }];
			const slots = {
				default: ({ items }: { items: unknown[] }) =>
					h("p", `${items.length} items`)
			};
			const wrapper = await open(mount({ options }, {}, { slots }));
			expect(wrapper.find("p").text()).toBe("2 items");
		});
	});

	describe("item slot", () => {
		it("replaces each option item", async () => {
			const options = [{ text: "Edit" }, "-", { text: "Delete" }];
			const slots = {
				item: ({ item, index }: { item: { text: string }; index: number }) =>
					h("p", `${index}: ${item.text}`)
			};
			const wrapper = await open(mount({ options }, {}, { slots }));
			expect(wrapper.findAll("p").map((p) => p.text())).toStrictEqual([
				"0: Edit",
				"2: Delete"
			]);
		});
	});
});
