import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Draggable from "./Draggable.vue";

// the options and callbacks Sortable was created with
type Sortable = {
	option: (name: string, value: unknown) => void;
	options: Record<string, unknown> & {
		[callback: `on${string}`]: (event: unknown, original?: unknown) => unknown;
	};
};

function panel() {
	return { drag: { start: vi.fn(), stop: vi.fn() } };
}

function mount(props = {}, attrs = {}, slots = {}, $panel = panel()) {
	return vueMount(Draggable, {
		props,
		attrs,
		slots: { default: "<p>A</p><p>B</p><p>C</p>", ...slots },
		attachTo: document.body,
		global: {
			mocks: { $panel }
		}
	});
}

// like the Panel's global mixin, which links each
// element to the component it is the root of
function link(wrapper: ReturnType<typeof mount>) {
	Object.assign(wrapper.element, { __vue__: wrapper.vm });
	return wrapper;
}

// the Sortable instance is created after a dynamic import,
// which can take longer than a tick before the module is cached
async function sortable(wrapper: ReturnType<typeof mount>) {
	await vi.waitUntil(() => wrapper.vm.sortable !== null);
	return wrapper.vm.sortable as unknown as Sortable;
}

describe("k-draggable", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-draggable");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("disabled prop", () => {
		it("drops the class", () => {
			expect(mount({ disabled: true }).classes()).not.toContain("k-draggable");
		});

		it("disables sorting", async () => {
			const instance = await sortable(mount({ disabled: true }));
			expect(instance.options.disabled).toBe(true);
		});
	});

	describe("element prop", () => {
		it("renders the element", () => {
			expect(mount({ element: "ul" }).element.tagName).toBe("UL");
		});
	});

	describe("group prop", () => {
		it("sets the group", async () => {
			const instance = await sortable(mount({ group: "blocks" }));
			expect(instance.options.group).toMatchObject({ name: "blocks" });
		});
	});

	describe("handle prop", () => {
		it("uses the sort handle", async () => {
			const instance = await sortable(mount({ handle: true }));
			expect(instance.options.handle).toBe(".k-sort-handle");
		});

		it("uses a custom handle", async () => {
			const instance = await sortable(mount({ handle: ".grip" }));
			expect(instance.options.handle).toBe(".grip");
		});
	});

	describe("options prop", () => {
		it("overrides the default options", async () => {
			const instance = await sortable(
				mount({ options: { draggable: ".item" } })
			);
			expect(instance.options.draggable).toBe(".item");
		});
	});

	// computed
	describe("dragOptions computed", () => {
		it("creates Sortable with the defaults", async () => {
			const main = document.createElement("main");
			main.classList.add("k-panel-main");
			document.body.append(main);
			const instance = await sortable(mount());
			main.remove();
			expect(instance.options).toMatchObject({
				disabled: false,
				draggable: ">*",
				fallbackClass: "k-sortable-fallback",
				fallbackOnBody: true,
				filter: ".k-draggable-footer",
				forceFallback: true,
				ghostClass: "k-sortable-ghost"
			});
			// toMatchObject() can't compare elements
			expect(instance.options.scroll).toBe(main);
		});
	});

	// watch
	describe("dragOptions watcher", () => {
		it("updates the changed options", async () => {
			const wrapper = mount();
			const instance = await sortable(wrapper);
			const option = vi.spyOn(instance, "option");
			await wrapper.setProps({ disabled: true } as never);
			expect(option).toHaveBeenCalledExactlyOnceWith("disabled", true);
		});
	});

	// methods
	describe("getInstance()", () => {
		it("finds the draggable of an element", () => {
			const wrapper = link(mount({ list: [] }));
			expect(wrapper.vm.getInstance(wrapper.element)).toBe(wrapper.vm);
		});
	});

	// events
	describe("events", () => {
		it("starts dragging", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, {}, $panel);
			const instance = await sortable(wrapper);
			const event = { item: wrapper.element.firstElementChild };
			instance.options.onStart?.(event as never);
			expect($panel.drag.start).toHaveBeenCalledWith("data", {});
			expect(wrapper.emitted("start")).toStrictEqual([[event]]);
		});

		it("stops dragging", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, {}, $panel);
			const instance = await sortable(wrapper);
			instance.options.onEnd?.({} as never);
			expect($panel.drag.stop).toHaveBeenCalledOnce();
			expect(wrapper.emitted("end")).toHaveLength(1);
		});

		it("moves an item within the list", async () => {
			const list = ["a", "b", "c"];
			const wrapper = mount({ list });
			const instance = await sortable(wrapper);
			instance.options.onUpdate?.({
				newIndex: 2,
				oldIndex: 0
			} as never);
			expect(list).toStrictEqual(["b", "c", "a"]);
			expect(wrapper.emitted("change")).toStrictEqual([
				[{ moved: { element: "a", newIndex: 2, oldIndex: 0 } }]
			]);
		});

		it("moves an item within a list with undraggable items", async () => {
			// the draggable indexes skip undraggable items (here: "b"),
			// which are still part of the list
			const list = ["a", "b", "c", "d"];
			const wrapper = mount({ list });
			const instance = await sortable(wrapper);
			instance.options.onUpdate?.({
				newDraggableIndex: 1,
				newIndex: 2,
				oldDraggableIndex: 2,
				oldIndex: 3
			} as never);
			expect(list).toStrictEqual(["a", "b", "d", "c"]);
			expect(wrapper.emitted("change")).toStrictEqual([
				[{ moved: { element: "d", newIndex: 2, oldIndex: 3 } }]
			]);
		});

		it("removes an item moved into another list", async () => {
			const list = ["a", "b", "c"];
			const wrapper = mount({ list });
			const instance = await sortable(wrapper);
			instance.options.onRemove?.({ oldIndex: 1 } as never);
			expect(list).toStrictEqual(["a", "c"]);
			expect(wrapper.emitted("change")).toStrictEqual([
				[{ removed: { element: "b", oldIndex: 1 } }]
			]);
		});

		it("adds an item from another list", async () => {
			const source = link(mount({ list: ["x", "y"] }));
			const list = ["a", "b"];
			const wrapper = mount({ list });
			const instance = await sortable(wrapper);
			instance.options.onAdd?.({
				from: source.element,
				newIndex: 1,
				oldIndex: 1
			} as never);
			expect(list).toStrictEqual(["a", "y", "b"]);
			expect(wrapper.emitted("change")).toStrictEqual([
				[{ added: { element: "y", newIndex: 1 } }]
			]);
		});

		it("leaves a list it doesn't sync alone", async () => {
			const wrapper = mount();
			const instance = await sortable(wrapper);
			instance.options.onUpdate?.({
				newIndex: 1,
				oldIndex: 0
			} as never);
			expect(wrapper.emitted("change")).toBeUndefined();
		});

		it("passes on the sorting", async () => {
			const wrapper = mount();
			const instance = await sortable(wrapper);
			instance.options.onSort?.({ newIndex: 1 } as never);
			expect(wrapper.emitted("sort")).toStrictEqual([[{ newIndex: 1 }]]);
		});

		it("allows any move without callback", async () => {
			const wrapper = mount();
			const instance = await sortable(wrapper);
			const dragged = wrapper.element.firstElementChild!;
			expect(instance.options.onMove?.({ dragged } as never, {} as never)).toBe(
				true
			);
		});

		it("asks the move callback with the data of both lists", async () => {
			const move = vi.fn(() => false);
			const source = link(mount({ data: { name: "source" }, list: [] }));
			const target = link(mount({ data: { name: "target" }, list: [], move }));
			const instance = await sortable(target);

			// the dragged element belongs to a component with props
			const dragged = source.element.firstElementChild!;
			Object.assign(dragged, { __vue__: { $props: { type: "text" } } });

			const event = { dragged, from: source.element, to: target.element };
			expect(instance.options.onMove?.(event as never, {} as never)).toBe(
				false
			);
			expect(move).toHaveBeenCalledWith({
				...event,
				draggedData: { type: "text" },
				fromData: { name: "source" },
				toData: { name: "target" }
			});
		});
	});

	// slots
	describe("footer slot", () => {
		it("renders the footer after the items", () => {
			const wrapper = mount({}, {}, { footer: `<footer>Add</footer>` });
			expect(wrapper.element.lastElementChild?.tagName).toBe("FOOTER");
		});

		it("doesn't move the footer", async () => {
			const wrapper = mount({}, {}, { footer: `<footer>Add</footer>` });
			const instance = await sortable(wrapper);
			const footer = wrapper.find("footer").element;
			footer.classList.add("k-draggable-footer");
			expect(
				instance.options.onMove?.({ dragged: footer } as never, {} as never)
			).toBe(false);
		});
	});
});
