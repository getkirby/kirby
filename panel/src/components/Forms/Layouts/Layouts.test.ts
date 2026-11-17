import {
	beforeEach,
	describe,
	expect,
	it,
	onTestFinished,
	vi
} from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import Layouts from "./Layouts.vue";

type Block = { content: Record<string, unknown>; id: string; type: string };
type Column = { blocks: Block[]; id: string; width: string };
type Layout = { attrs: Record<string, unknown>; columns: Column[]; id: string };

const Draggable = {
	name: "k-draggable",
	props: ["handle", "list"],
	emits: ["sort"],
	template: "<div><slot /></div>"
};
const LayoutStub = {
	name: "k-layout",
	props: [
		"attrs",
		"columns",
		"disabled",
		"endpoints",
		"fieldsetGroups",
		"fieldsets",
		"id",
		"isSelected",
		"layouts",
		"settings"
	],
	emits: [
		"append",
		"change",
		"copy",
		"duplicate",
		"paste",
		"prepend",
		"remove",
		"select",
		"updateAttrs",
		"updateColumn"
	],
	template: `<section class="k-layout" />`
};

const fieldsets = { text: { name: "Text", type: "text" } };
const layouts = [["1/1"], ["1/2", "1/2"]];

function column(id: string, width: string, ...blocks: string[]): Column {
	return {
		blocks: blocks.map((id) => ({ content: {}, id, type: "text" })),
		id,
		width
	};
}

// a: one full column with block x,
// b: two halves, only the second one with block y
function rows(): Layout[] {
	return [
		{ attrs: {}, columns: [column("a1", "1/1", "x")], id: "a" },
		{
			attrs: { class: "b" },
			columns: [column("b1", "1/2"), column("b2", "1/2", "y")],
			id: "b"
		}
	];
}

const api = { post: vi.fn() };

function panel() {
	return {
		dialog: { close: vi.fn(), open: vi.fn() },
		notification: { success: vi.fn() }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(Layouts, {
		props: {
			endpoints: { field: "pages/a/fields/layout" },
			fieldsets,
			layouts,
			value: rows(),
			...props
		},
		attrs,
		global: {
			mocks: { $api: api, $panel },
			stubs: { "k-draggable": Draggable, "k-layout": LayoutStub }
		}
	});
}

function items(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(LayoutStub);
}

function ids(list: { id: string }[] | undefined) {
	return list?.map((item) => item.id);
}

function lastInput(wrapper: ReturnType<typeof mount>) {
	return wrapper.emitted("input")?.at(-1)?.[0] as Layout[] | undefined;
}

function dialog($panel: ReturnType<typeof panel>) {
	return $panel.dialog.open.mock.calls.at(-1)?.[0];
}

function clipboard(wrapper: ReturnType<typeof mount>) {
	const read = vi.spyOn(wrapper.vm.$helper.clipboard, "read");
	const write = vi.spyOn(wrapper.vm.$helper.clipboard, "write");
	read.mockImplementation(() => "[]");
	write.mockImplementation(() => true);
	onTestFinished(() => {
		read.mockRestore();
		write.mockRestore();
	});
	return { read, write };
}

describe("k-layouts", () => {
	beforeEach(() => {
		// the field endpoint returns an empty layout row
		// for the requested columns
		api.post.mockImplementation(
			async (path: string, { columns }: { columns: string[] }) => {
				if (path.endsWith("/paste")) {
					return [{ attrs: {}, columns: [column("p1", "1/1")], id: "p" }];
				}

				return {
					attrs: {},
					columns: columns.map((width, index) => column("n" + index, width)),
					id: "n"
				};
			}
		);
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders the layouts in a sortable list", () => {
			const value = rows();
			const wrapper = mount({ value });
			const draggable = wrapper.findComponent(Draggable);
			expect(draggable.classes()).toContain("k-layouts");
			expect(draggable.props()).toStrictEqual({ handle: true, list: value });
			expect(items(wrapper).map((item) => item.props("id"))).toStrictEqual([
				"a",
				"b"
			]);
		});

		it("passes each layout with the field setup", () => {
			const wrapper = mount();
			expect(items(wrapper)[1].props()).toStrictEqual({
				attrs: { class: "b" },
				columns: rows()[1].columns,
				disabled: false,
				endpoints: { field: "pages/a/fields/layout" },
				fieldsetGroups: undefined,
				fieldsets,
				id: "b",
				isSelected: false,
				layouts,
				settings: undefined
			});
		});

		it("hints at missing fieldsets", () => {
			const wrapper = mount({ fieldsets: {} });
			const empty = wrapper.find("k-empty");
			expect(empty.classes()).toContain("k-layout-empty");
			expect(empty.attributes("icon")).toBe("dashboard");
			expect(empty.text()).toBe("field.blocks.fieldsets.empty");
			expect(wrapper.findComponent(Draggable).exists()).toBe(false);
		});

		it("shows an empty state without layouts", () => {
			const wrapper = mount({ value: [] });
			const empty = wrapper.find("k-empty");
			expect(empty.classes()).toContain("k-layout-empty");
			expect(empty.text()).toBe("field.layout.empty");
			expect(wrapper.findComponent(Draggable).exists()).toBe(false);
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the layouts", () => {
			const wrapper = mount({ disabled: true });
			expect(items(wrapper)[0].props("disabled")).toBe(true);
		});

		it("adds no layout from the empty state", async () => {
			const $panel = panel();
			const wrapper = mount({ disabled: true, value: [] }, {}, $panel);
			await wrapper.find("k-empty").trigger("click");
			expect($panel.dialog.open).not.toHaveBeenCalled();
		});
	});

	describe("empty prop", () => {
		it("replaces the empty text", () => {
			const wrapper = mount({ empty: "No rows yet", value: [] });
			expect(wrapper.find("k-empty").text()).toBe("No rows yet");
		});
	});

	describe("fieldsetGroups prop", () => {
		it("passes the groups to each layout", () => {
			const fieldsetGroups = { text: { label: "Text", sets: ["text"] } };
			const wrapper = mount({ fieldsetGroups });
			expect(items(wrapper)[0].props("fieldsetGroups")).toStrictEqual(
				fieldsetGroups
			);
		});
	});

	describe("settings prop", () => {
		it("passes the settings to each layout", () => {
			const settings = { tabs: {} };
			const wrapper = mount({ settings });
			expect(items(wrapper)[0].props("settings")).toStrictEqual(settings);
		});
	});

	// watch
	describe("value watcher", () => {
		it("renders a new value", async () => {
			const wrapper = mount();
			await wrapper.setProps({ value: [rows()[1]] } as never);
			expect(items(wrapper).map((item) => item.props("id"))).toStrictEqual([
				"b"
			]);
		});
	});

	// methods
	describe("copy()", () => {
		it("copies a layout", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			const { write } = clipboard(wrapper);
			const event = new Event("copy");
			wrapper.vm.copy(event, 1);
			expect(write).toHaveBeenCalledWith(JSON.stringify(rows()[1]), event);
			expect($panel.notification.success).toHaveBeenCalledWith({
				icon: "template",
				message: "copy.success.multiple"
			});
		});

		it("counts the copied layouts", () => {
			const t = vi.spyOn(window.panel, "t");
			onTestFinished(() => t.mockRestore());
			const wrapper = mount();
			clipboard(wrapper);
			wrapper.vm.copy(new Event("copy"), 0);
			expect(t).toHaveBeenLastCalledWith("copy.success.multiple", {
				count: 1
			});
			wrapper.vm.copy(new Event("copy"));
			expect(t).toHaveBeenLastCalledWith("copy.success.multiple", {
				count: 2
			});
		});

		it("copies all layouts without index", () => {
			const wrapper = mount();
			const { write } = clipboard(wrapper);
			wrapper.vm.copy(new Event("copy"));
			expect(write.mock.calls[0][0]).toBe(JSON.stringify(rows()));
		});

		it("copies nothing without layouts", () => {
			const $panel = panel();
			const wrapper = mount({ value: [] }, {}, $panel);
			const { write } = clipboard(wrapper);
			expect(wrapper.vm.copy(new Event("copy"))).toBe(false);
			expect(write).not.toHaveBeenCalled();
			expect($panel.notification.success).not.toHaveBeenCalled();
		});
	});

	describe("change()", () => {
		it("opens the layout selector with the current layout", () => {
			const $panel = panel();
			const wrapper = mount({ selector: { size: "large" } }, {}, $panel);
			wrapper.vm.change(1, rows()[1]);
			expect(dialog($panel)).toMatchObject({
				component: "k-layout-selector",
				props: {
					label: "field.layout.change",
					layouts,
					selector: { size: "large" },
					value: ["1/2", "1/2"]
				}
			});
		});

		it("changes the layout on submit", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.change(1, rows()[1]);
			dialog($panel).on.submit(["1/1"]);
			await flushPromises();
			expect(api.post).toHaveBeenCalledWith("pages/a/fields/layout/layout", {
				attrs: { class: "b" },
				columns: ["1/1"]
			});
			expect($panel.dialog.close).toHaveBeenCalledOnce();
		});
	});

	describe("duplicate()", () => {
		it("inserts a copy with new ids after the layout", () => {
			const value = rows();
			const wrapper = mount({ value });
			wrapper.vm.duplicate(0, value[0]);

			const [original, copy] = lastInput(wrapper) ?? [];
			expect(lastInput(wrapper)).toHaveLength(3);
			expect(original).toStrictEqual(rows()[0]);
			expect(copy.id).not.toBe("a");
			expect(copy.columns[0].id).not.toBe("a1");
			expect(copy.columns[0].width).toBe("1/1");
			expect(copy.columns[0].blocks[0].id).not.toBe("x");
			expect(copy.columns[0].blocks[0].type).toBe("text");
		});
	});

	describe("onAdd()", () => {
		it("inserts a new layout at the next index", async () => {
			const wrapper = mount();
			wrapper.vm.nextIndex = 1 as never;
			await wrapper.vm.onAdd(["1/2", "1/2"]);
			expect(api.post).toHaveBeenCalledWith("pages/a/fields/layout/layout", {
				columns: ["1/2", "1/2"]
			});
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "n", "b"]);
		});
	});

	describe("onChange()", () => {
		function change(
			wrapper: ReturnType<typeof mount>,
			columns: string[],
			rowIndex: number
		) {
			const layout = wrapper.vm.rows[rowIndex] as Layout;
			const widths = layout.columns.map((column) => column.width);
			const layoutIndex = layouts.findIndex(
				(columns) => columns.toString() === widths.toString()
			);

			return wrapper.vm.onChange(columns, layoutIndex, {
				layout,
				layoutIndex,
				rowIndex
			});
		}

		it("moves the blocks into the new columns", async () => {
			const wrapper = mount();
			await change(wrapper, ["1/1"], 1);

			const [a, b] = lastInput(wrapper) ?? [];
			expect(lastInput(wrapper)).toHaveLength(2);
			expect(a.id).toBe("a");
			expect(b.id).not.toBe("b");
			expect(b.columns.map((column) => column.width)).toStrictEqual(["1/1"]);
			expect(ids(b.columns[0].blocks)).toStrictEqual(["y"]);
		});

		it("keeps the attributes", async () => {
			const wrapper = mount();
			await change(wrapper, ["1/1"], 1);
			expect(api.post).toHaveBeenCalledWith("pages/a/fields/layout/layout", {
				attrs: { class: "b" },
				columns: ["1/1"]
			});
		});

		it("fills the new columns in order", async () => {
			const wrapper = mount();
			await change(wrapper, ["1/2", "1/2"], 0);

			const [a] = lastInput(wrapper) ?? [];
			expect(ids(a.columns[0].blocks)).toStrictEqual(["x"]);
			expect(a.columns[1].blocks).toStrictEqual([]);
		});

		it("adds rows for blocks that don't fit", async () => {
			const value = [
				{
					attrs: {},
					columns: [
						column("c1", "1/3", "x"),
						column("c2", "1/3", "y"),
						column("c3", "1/3", "z")
					],
					id: "c"
				}
			];
			const wrapper = mount({ value });
			await change(wrapper, ["1/2", "1/2"], 0);

			const result = lastInput(wrapper) ?? [];
			expect(result).toHaveLength(2);
			expect(result[0].id).not.toBe(result[1].id);
			expect(
				result.map((row) => row.columns.map((column) => ids(column.blocks)))
			).toStrictEqual([
				[["x"], ["y"]],
				[["z"], []]
			]);
		});

		it("switches a layout without blocks", async () => {
			const value = [{ attrs: {}, columns: [column("c1", "1/1")], id: "c" }];
			const wrapper = mount({ value });
			await change(wrapper, ["1/2", "1/2"], 0);
			expect(lastInput(wrapper)).toStrictEqual([
				{
					attrs: {},
					columns: [column("n0", "1/2"), column("n1", "1/2")],
					id: "n"
				}
			]);
		});
	});

	describe("paste()", () => {
		it("pastes the clipboard at the end", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			const { read } = clipboard(wrapper);
			const event = new Event("paste");
			await wrapper.vm.paste(event);
			expect(read).toHaveBeenCalledWith(event);
			expect(api.post).toHaveBeenCalledWith(
				"pages/a/fields/layout/layout/paste",
				{ json: "[]" }
			);
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "b", "p"]);
			expect($panel.notification.success).toHaveBeenCalledWith({
				icon: "download",
				message: "paste.success"
			});
		});

		it("pastes the clipboard at the index", async () => {
			const wrapper = mount();
			clipboard(wrapper);
			await wrapper.vm.paste(new Event("paste"), 0);
			expect(ids(lastInput(wrapper))).toStrictEqual(["p", "a", "b"]);
		});

		it("pastes nothing for an empty clipboard", async () => {
			api.post.mockResolvedValue([]);
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			clipboard(wrapper);
			await wrapper.vm.paste(new Event("paste"));
			expect(wrapper.emitted("input")).toBeUndefined();
			expect($panel.notification.success).toHaveBeenCalledOnce();
		});
	});

	describe("pasteboard()", () => {
		it("opens the pasteboard that pastes at the index", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			clipboard(wrapper);
			wrapper.vm.pasteboard(1);
			expect(dialog($panel).component).toBe("k-block-pasteboard");
			await dialog($panel).on.paste(new Event("paste"));
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "p", "b"]);
		});
	});

	describe("remove()", () => {
		it("removes the layout with the same id", () => {
			const wrapper = mount();
			wrapper.vm.remove(rows()[0]);
			expect(ids(lastInput(wrapper))).toStrictEqual(["b"]);
		});

		it("keeps all layouts for an unknown one", () => {
			const wrapper = mount();
			wrapper.vm.remove({ id: "c" });
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "b"]);
		});
	});

	describe("removeAll()", () => {
		it("removes all layouts after confirmation", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.removeAll();
			expect(dialog($panel)).toMatchObject({
				component: "k-remove-dialog",
				props: { text: "field.layout.delete.confirm.all" }
			});
			expect(wrapper.emitted("input")).toBeUndefined();

			dialog($panel).on.submit();
			expect(lastInput(wrapper)).toStrictEqual([]);
			expect($panel.dialog.close).toHaveBeenCalledOnce();
		});
	});

	describe("select()", () => {
		it("adds the only layout right away", async () => {
			const $panel = panel();
			const wrapper = mount({ layouts: [["1/3", "2/3"]] }, {}, $panel);
			await wrapper.vm.select(1);
			expect($panel.dialog.open).not.toHaveBeenCalled();
			expect(api.post).toHaveBeenCalledWith("pages/a/fields/layout/layout", {
				columns: ["1/3", "2/3"]
			});
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "n", "b"]);
		});

		it("opens the layout selector", () => {
			const $panel = panel();
			const wrapper = mount({ selector: { columns: 2 } }, {}, $panel);
			wrapper.vm.select(1);
			expect(dialog($panel)).toMatchObject({
				component: "k-layout-selector",
				props: { layouts, selector: { columns: 2 }, value: null }
			});
		});

		it("adds the selected layout at the index", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.select(1);
			dialog($panel).on.submit(["1/2", "1/2"]);
			await flushPromises();
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "n", "b"]);
			expect($panel.dialog.close).toHaveBeenCalledOnce();
		});
	});

	describe("updateAttrs()", () => {
		it("replaces the attributes of a layout", () => {
			const wrapper = mount();
			wrapper.vm.updateAttrs(0, { class: "a" });
			expect(lastInput(wrapper)?.[0].attrs).toStrictEqual({ class: "a" });
		});
	});

	describe("updateColumn()", () => {
		it("replaces the blocks of a column", () => {
			const wrapper = mount();
			const blocks = column("c", "1/1", "z").blocks;
			wrapper.vm.updateColumn({ blocks, columnIndex: 0, index: 1 });
			expect(lastInput(wrapper)?.[1].columns[0].blocks).toStrictEqual(blocks);
		});
	});

	describe("updateIds()", () => {
		it("replaces the ids of layouts, columns and blocks", () => {
			const wrapper = mount();
			const result = wrapper.vm.updateIds(rows()) as Layout[];
			const all = result.flatMap((layout) => [
				layout.id,
				...layout.columns.flatMap((column) => [
					column.id,
					...column.blocks.map((block) => block.id)
				])
			]);
			expect(all).toHaveLength(7);
			expect(new Set(all).size).toBe(7);
			expect(all).not.toContain("a");
			expect(all).not.toContain("b2");
			expect(all).not.toContain("y");
		});

		it("wraps a single layout", () => {
			const wrapper = mount();
			expect(wrapper.vm.updateIds(rows()[0])).toHaveLength(1);
		});
	});

	// events
	describe("events", () => {
		it("emits the sorted layouts", () => {
			const wrapper = mount();
			wrapper.findComponent(Draggable).vm.$emit("sort");
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "b"]);
		});

		it("adds a layout from the empty state", async () => {
			const $panel = panel();
			const wrapper = mount({ value: [] }, {}, $panel);
			await wrapper.find("k-empty").trigger("click");
			expect(wrapper.vm.nextIndex).toBe(0);
			expect(dialog($panel).component).toBe("k-layout-selector");
		});

		it("adds a layout after a layout on append", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			items(wrapper)[0].vm.$emit("append");
			expect(wrapper.vm.nextIndex).toBe(1);
			expect(dialog($panel).component).toBe("k-layout-selector");
		});

		it("adds a layout before a layout on prepend", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			items(wrapper)[1].vm.$emit("prepend");
			expect(wrapper.vm.nextIndex).toBe(1);
			expect(dialog($panel).component).toBe("k-layout-selector");
		});

		it("changes a layout on change", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			items(wrapper)[1].vm.$emit("change");
			expect(dialog($panel).props.value).toStrictEqual(["1/2", "1/2"]);
		});

		it("copies a layout on copy", () => {
			const wrapper = mount();
			const { write } = clipboard(wrapper);
			items(wrapper)[1].vm.$emit("copy", new Event("copy"));
			expect(write.mock.calls[0][0]).toBe(JSON.stringify(rows()[1]));
		});

		it("duplicates a layout on duplicate", () => {
			const wrapper = mount();
			items(wrapper)[1].vm.$emit("duplicate");
			expect(lastInput(wrapper)?.[1].id).toBe("b");
			expect(lastInput(wrapper)?.[2].columns[1].blocks).toHaveLength(1);
		});

		it("pastes after a layout on paste", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			clipboard(wrapper);
			items(wrapper)[0].vm.$emit("paste");
			await dialog($panel).on.paste(new Event("paste"));
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "p", "b"]);
		});

		it("removes a layout on remove", () => {
			const wrapper = mount();
			items(wrapper)[0].vm.$emit("remove");
			expect(ids(lastInput(wrapper))).toStrictEqual(["b"]);
		});

		it("selects a layout on select", async () => {
			const wrapper = mount();
			await items(wrapper)[1].vm.$emit("select");
			expect(
				items(wrapper).map((item) => item.props("isSelected"))
			).toStrictEqual([false, true]);
		});

		it("updates the attributes on update-attrs", () => {
			const wrapper = mount();
			items(wrapper)[1].vm.$emit("updateAttrs", { class: "c" });
			expect(lastInput(wrapper)?.[1].attrs).toStrictEqual({ class: "c" });
		});

		it("updates a column on update-column", () => {
			const wrapper = mount();
			const blocks = column("c", "1/1", "z").blocks;
			items(wrapper)[1].vm.$emit("updateColumn", {
				blocks,
				column: rows()[1].columns[1],
				columnIndex: 1
			});
			expect(ids(lastInput(wrapper)?.[1].columns[1].blocks)).toStrictEqual([
				"z"
			]);
		});
	});
});
