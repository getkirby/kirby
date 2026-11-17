import {
	afterEach,
	beforeEach,
	describe,
	expect,
	it,
	onTestFinished,
	vi
} from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import Blocks from "./Blocks.vue";

type Block = {
	content: Record<string, unknown>;
	id: string;
	isHidden?: boolean;
	type: string;
};

const Editor = {
	name: "BlockEditor",
	methods: { merge: vi.fn() },
	template: "<span />"
};
const BlockStub = {
	name: "k-block",
	components: { BlockEditor: Editor },
	props: [
		"content",
		"disabled",
		"fieldset",
		"id",
		"isBatched",
		"isFull",
		"isHidden",
		"isLastSelected",
		"isMergable",
		"isSelected",
		"next",
		"prev",
		"type"
	],
	emits: [
		"append",
		"choose-to-append",
		"choose-to-convert",
		"choose-to-prepend",
		"click",
		"close",
		"copy",
		"duplicate",
		"focus",
		"hide",
		"merge",
		"open",
		"paste",
		"prepend",
		"remove",
		"remove-selected",
		"select-down",
		"select-up",
		"show",
		"sort-down",
		"sort-up",
		"split",
		"update"
	],
	methods: {
		collapse: vi.fn(),
		expand: vi.fn(),
		focus: vi.fn(),
		isCollapsed: vi.fn(() => false),
		isCollapsible: vi.fn(() => true),
		isExpandable: vi.fn(() => true),
		open: vi.fn()
	},
	template: `<div class="k-block"><BlockEditor ref="editor" /></div>`
};
const Draggable = {
	name: "k-draggable",
	props: ["data", "group", "handle", "list", "move"],
	emits: ["sort"],
	template: "<div><slot /></div>"
};

function fields(...names: string[]) {
	return {
		content: {
			fields: Object.fromEntries(names.map((name) => [name, { type: "text" }]))
		}
	};
}

const fieldsets = {
	heading: {
		name: "Heading",
		tabs: fields("text"),
		type: "heading",
		wysiwyg: true
	},
	image: { name: "Image", tabs: fields("alt"), type: "image" },
	text: { name: "Text", tabs: fields("text"), type: "text", wysiwyg: true }
};

function blocks(): Block[] {
	return [
		{ content: { text: "A" }, id: "a", type: "text" },
		{ content: { text: "B" }, id: "b", type: "text" },
		{ content: { alt: "C" }, id: "c", type: "image" }
	];
}

const api = { get: vi.fn(), post: vi.fn() };
const events = { off: vi.fn(), on: vi.fn() };

function panel() {
	return {
		dialog: { close: vi.fn(), isOpen: false, open: vi.fn() },
		notification: { success: vi.fn() }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(Blocks, {
		props: {
			endpoints: { field: "pages/a/fields/text" },
			fieldsets,
			value: blocks(),
			...props
		},
		attrs,
		attachTo: document.body,
		global: {
			mocks: { $api: api, $events: events, $panel },
			stubs: { "k-block": BlockStub, "k-draggable": Draggable }
		}
	});
}

function items(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(BlockStub);
}

function ids(list: Block[] | undefined) {
	return list?.map((block) => block.id);
}

function lastInput(wrapper: ReturnType<typeof mount>) {
	return wrapper.emitted("input")?.at(-1)?.[0] as Block[] | undefined;
}

// selected starts as an empty array, which TypeScript types as never[]
function selection(wrapper: ReturnType<typeof mount>, ids: string[]) {
	wrapper.vm.selected = ids as never[];
}

function dialog($panel: ReturnType<typeof panel>) {
	return $panel.dialog.open.mock.calls.at(-1)?.[0];
}

describe("k-blocks", () => {
	beforeEach(() => {
		api.get.mockImplementation(async (path: string) => ({
			content: { text: "" },
			id: "new",
			type: path.split("/").pop()
		}));
		api.post.mockResolvedValue([{ content: {}, id: "pasted", type: "text" }]);
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-blocks");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("marks an empty list", () => {
			const wrapper = mount({ value: [] });
			expect(wrapper.attributes("data-empty")).toBe("true");
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the blocks", () => {
			const wrapper = mount({ disabled: true });
			expect(items(wrapper).every((item) => item.props("disabled"))).toBe(true);
			expect(wrapper.attributes("data-disabled")).toBe("true");
		});
	});

	describe("empty prop", () => {
		it("shows the default empty text", () => {
			const wrapper = mount({ value: [] });
			expect(wrapper.find(".k-blocks-empty").text()).toBe("field.blocks.empty");
		});

		it("shows the empty text", () => {
			const wrapper = mount({ empty: "No blocks yet", value: [] });
			expect(wrapper.find(".k-blocks-empty").text()).toBe("No blocks yet");
		});
	});

	describe("fieldsets prop", () => {
		it("passes the fieldset of each block", () => {
			const wrapper = mount();
			expect(items(wrapper)[2].props("fieldset")).toStrictEqual(
				fieldsets.image
			);
		});

		it("explains missing fieldsets", () => {
			const wrapper = mount({ fieldsets: {} });
			expect(items(wrapper)).toHaveLength(0);
			expect(wrapper.find("k-empty").text()).toBe(
				"field.blocks.fieldsets.empty"
			);
		});
	});

	describe("group prop", () => {
		it("passes the group to the k-draggable", () => {
			const wrapper = mount({ group: "layout" });
			expect(wrapper.findComponent(Draggable).props("group")).toBe("layout");
		});
	});

	describe("value prop", () => {
		it("renders a block for each value", () => {
			const wrapper = mount();
			expect(items(wrapper).map((item) => item.props("id"))).toStrictEqual([
				"a",
				"b",
				"c"
			]);
		});

		it("hides hidden blocks", () => {
			const value = blocks();
			value[1].isHidden = true;
			const wrapper = mount({ value });
			expect(items(wrapper)[1].props("isHidden")).toBe(true);
		});
	});

	// computed
	describe("draggableOptions computed", () => {
		it("sorts the blocks by their handles", () => {
			const wrapper = mount({ max: 3 });
			expect(wrapper.findComponent(Draggable).props()).toMatchObject({
				data: { fieldsets, isFull: true },
				handle: ".k-sort-handle",
				move: wrapper.vm.move
			});
		});
	});

	describe("isFull computed", () => {
		it("is never full without a maximum", () => {
			const wrapper = mount();
			expect(wrapper.vm.isFull).toBe(false);
		});

		it("is full at the maximum", () => {
			const wrapper = mount({ max: 3 });
			expect(items(wrapper)[0].props("isFull")).toBe(true);
		});
	});

	describe("isMergable computed", () => {
		it("merges several blocks of the same type", () => {
			const wrapper = mount();
			selection(wrapper, ["a", "b"]);
			expect(wrapper.vm.isMergable).toBe(true);
		});

		it("merges no single block", () => {
			const wrapper = mount();
			selection(wrapper, ["a"]);
			expect(wrapper.vm.isMergable).toBe(false);
		});

		it("merges no blocks of different types", () => {
			const wrapper = mount();
			selection(wrapper, ["a", "c"]);
			expect(wrapper.vm.isMergable).toBe(false);
		});
	});

	// watch
	describe("value watcher", () => {
		it("renders the blocks of a new value", async () => {
			const wrapper = mount();
			await wrapper.setProps({ value: [blocks()[2]] } as never);
			expect(items(wrapper).map((item) => item.props("id"))).toStrictEqual([
				"c"
			]);
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		afterEach(() => {
			vi.useRealTimers();
		});

		it("listens to global events until unmounted", () => {
			const wrapper = mount();
			const names = events.on.mock.calls.map(([name]) => name);
			expect(names).toStrictEqual([
				"blur",
				"click",
				"focus",
				"copy",
				"keydown",
				"keyup",
				"paste"
			]);
			wrapper.unmount();
			expect(events.off.mock.calls.map(([name]) => name)).toStrictEqual(names);
		});

		it("focuses the first block with autofocus", () => {
			vi.useFakeTimers();
			mount({ autofocus: true });
			vi.advanceTimersByTime(100);
			expect(BlockStub.methods.focus).toHaveBeenCalledOnce();
		});
	});

	// methods
	describe("add()", () => {
		it("inserts a new block of the type and focuses it", async () => {
			const wrapper = mount();
			await wrapper.vm.add("heading", 1);
			await flushPromises();
			expect(api.get).toHaveBeenCalledWith(
				"pages/a/fields/text/fieldsets/heading"
			);
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "new", "b", "c"]);
			expect(BlockStub.methods.focus).toHaveBeenCalledOnce();
		});

		it("opens a new block that is no wysiwyg block", async () => {
			const wrapper = mount();
			await wrapper.vm.add("image", 3);
			expect(BlockStub.methods.open).toHaveBeenCalledOnce();
		});
	});

	describe("choose()", () => {
		it("adds the only fieldset right away", async () => {
			const $panel = panel();
			const wrapper = mount(
				{ fieldsets: { text: fieldsets.text } },
				{},
				$panel
			);
			await wrapper.vm.choose(0);
			expect($panel.dialog.open).not.toHaveBeenCalled();
			expect(ids(lastInput(wrapper))).toStrictEqual(["new", "a", "b", "c"]);
		});

		it("asks for the fieldset to add", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.choose(1);
			const options = dialog($panel);
			expect(options.component).toBe("k-block-selector");
			expect(options.props.fieldsets).toStrictEqual(fieldsets);
			await options.on.submit("heading");
			await flushPromises();
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "new", "b", "c"]);
			expect($panel.dialog.close).toHaveBeenCalledOnce();
		});
	});

	describe("chooseToConvert()", () => {
		it("asks for another fieldset and converts the block", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.chooseToConvert(blocks()[0]);
			const options = dialog($panel);
			expect(options.props).toMatchObject({
				disabledFieldsets: ["text"],
				headline: "field.blocks.changeType"
			});
			await options.on.submit("heading");
			await flushPromises();
			expect(lastInput(wrapper)?.[0]).toMatchObject({
				content: { text: "A" },
				id: "a",
				type: "heading"
			});
		});
	});

	describe("collapseAll()", () => {
		it("collapses each block", () => {
			const wrapper = mount();
			wrapper.vm.collapseAll();
			expect(BlockStub.methods.collapse).toHaveBeenCalledTimes(3);
		});
	});

	describe("convert()", () => {
		it("keeps the content of fields with the same name and type", async () => {
			const wrapper = mount();
			await wrapper.vm.convert("image", blocks()[0]);
			expect(lastInput(wrapper)?.[0]).toStrictEqual({
				content: { text: "" },
				id: "a",
				type: "image"
			});
		});

		it("converts nothing to an unknown fieldset", async () => {
			const wrapper = mount();
			expect(await wrapper.vm.convert("video", blocks()[0])).toBe(false);
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("copy()", () => {
		function clipboard(wrapper: ReturnType<typeof mount>) {
			const write = vi.spyOn(wrapper.vm.$helper.clipboard, "write");
			write.mockImplementation(() => true);
			onTestFinished(() => write.mockRestore());
			return write;
		}

		it("copies the selected blocks", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			const write = clipboard(wrapper);
			selection(wrapper, ["c", "a"]);
			wrapper.vm.copy();
			expect(ids(write.mock.calls[0][0] as Block[])).toStrictEqual(["a", "c"]);
			expect($panel.notification.success).toHaveBeenCalledOnce();
		});

		it("copies nothing without selection", () => {
			const wrapper = mount();
			const write = clipboard(wrapper);
			expect(wrapper.vm.copy()).toBe(false);
			expect(write).not.toHaveBeenCalled();
		});

		it("copies all blocks and deselects them again", () => {
			const wrapper = mount();
			const write = clipboard(wrapper);
			wrapper.vm.copyAll();
			expect(ids(write.mock.calls[0][0] as Block[])).toStrictEqual([
				"a",
				"b",
				"c"
			]);
			expect(wrapper.vm.selected).toStrictEqual([]);
		});
	});

	describe("duplicate()", () => {
		it("inserts a copy with a new id after the block", async () => {
			const wrapper = mount();
			await wrapper.vm.duplicate(blocks()[0], 0);
			const list = lastInput(wrapper);
			expect(list).toHaveLength(4);
			expect(list?.[1].content).toStrictEqual({ text: "A" });
			expect(list?.[1].id).not.toBe("a");
		});
	});

	describe("expandAll()", () => {
		it("expands each block", () => {
			const wrapper = mount();
			wrapper.vm.expandAll();
			expect(BlockStub.methods.expand).toHaveBeenCalledTimes(3);
		});
	});

	describe("fieldset()", () => {
		it("falls back to a box for an unknown type", () => {
			const wrapper = mount();
			expect(wrapper.vm.fieldset({ type: "video" })).toStrictEqual({
				icon: "box",
				name: "video",
				tabs: { content: { fields: {} } },
				type: "video"
			});
		});
	});

	describe("focus()", () => {
		it("selects and focuses the block", () => {
			const wrapper = mount();
			wrapper.vm.focus(blocks()[1]);
			expect(wrapper.vm.selected).toStrictEqual(["b"]);
			expect(BlockStub.methods.focus).toHaveBeenCalledOnce();
		});
	});

	describe("hide()", () => {
		it("hides the block", () => {
			const value = blocks();
			const wrapper = mount({ value });
			wrapper.vm.hide(value[0]);
			expect(lastInput(wrapper)?.[0].isHidden).toBe(true);
		});
	});

	describe("isCollapsible()", () => {
		it("asks the blocks", () => {
			const wrapper = mount();
			expect(wrapper.vm.isCollapsible()).toBe(true);
			expect(wrapper.vm.isExpandable()).toBe(true);
			expect(wrapper.vm.isFullyCollapsed()).toBe(false);
			expect(wrapper.vm.isFullyExpanded()).toBe(true);
		});
	});

	describe("merge()", () => {
		it("merges the selected blocks into the first one", async () => {
			const wrapper = mount();
			selection(wrapper, ["b", "a"]);
			await wrapper.vm.merge();
			expect(
				ids(Editor.methods.merge.mock.calls[0][0] as Block[])
			).toStrictEqual(["a", "b"]);
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "c"]);
		});
	});

	describe("move()", () => {
		it("moves blocks within the same field", () => {
			const wrapper = mount();
			expect(wrapper.vm.move({ from: 1, to: 1 })).toBe(true);
		});

		it("moves no blocks of an unsupported type into another field", () => {
			const wrapper = mount();
			const event = {
				draggedData: { type: "video" },
				from: 1,
				to: 2,
				toData: { fieldsets, isFull: false }
			};
			expect(wrapper.vm.move(event)).toBe(false);
		});

		it("moves no blocks into a full field", () => {
			const wrapper = mount();
			const event = {
				draggedData: { type: "text" },
				from: 1,
				to: 2,
				toData: { fieldsets, isFull: true }
			};
			expect(wrapper.vm.move(event)).toBe(false);
		});
	});

	describe("onClickBlock()", () => {
		it("toggles the selection with a modifier key", () => {
			const wrapper = mount();
			const event = new MouseEvent("click", { metaKey: true });
			wrapper.vm.isMultiSelectKey = true;
			wrapper.vm.onClickBlock(blocks()[1], event);
			expect(wrapper.vm.selected).toStrictEqual(["b"]);
			wrapper.vm.onClickBlock(blocks()[1], event);
			expect(wrapper.vm.selected).toStrictEqual([]);
		});
	});

	describe("onFocus()", () => {
		it("selects the focused block", () => {
			const wrapper = mount();
			wrapper.vm.onFocus(blocks()[2]);
			expect(wrapper.vm.selected).toStrictEqual(["c"]);
		});

		it("keeps the selection while multi-selecting", () => {
			const wrapper = mount();
			selection(wrapper, ["a"]);
			wrapper.vm.isMultiSelectKey = true;
			wrapper.vm.onFocus(blocks()[2]);
			expect(wrapper.vm.selected).toStrictEqual(["a"]);
		});
	});

	describe("onKey()", () => {
		it("tracks the modifier keys", () => {
			const wrapper = mount();
			wrapper.vm.onKey(new KeyboardEvent("keydown", { altKey: true }));
			expect(wrapper.vm.isMultiSelectKey).toBe(true);
		});

		it("reduces the selection to the first block on escape", async () => {
			const wrapper = mount();
			selection(wrapper, ["b", "c"]);
			await wrapper.vm.onKey(new KeyboardEvent("keydown", { code: "Escape" }));
			expect(wrapper.vm.selected).toStrictEqual(["b"]);
		});
	});

	describe("paste()", () => {
		function clipboard(wrapper: ReturnType<typeof mount>) {
			const read = vi.spyOn(wrapper.vm.$helper.clipboard, "read");
			read.mockImplementation(() => "<p>Pasted</p>");
			onTestFinished(() => read.mockRestore());
		}

		it("converts the clipboard into blocks after the selection", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			clipboard(wrapper);
			selection(wrapper, ["a"]);
			await wrapper.vm.paste(new Event("paste"));
			expect(api.post).toHaveBeenCalledWith("pages/a/fields/text/paste", {
				html: "<p>Pasted</p>"
			});
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "pasted", "b", "c"]);
			expect($panel.notification.success).toHaveBeenCalledOnce();
		});

		it("pastes at the end without selection", async () => {
			const wrapper = mount();
			clipboard(wrapper);
			await wrapper.vm.paste(new Event("paste"));
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "b", "c", "pasted"]);
		});

		it("pastes no more blocks than the maximum allows", async () => {
			api.post.mockResolvedValue([
				{ content: {}, id: "x", type: "text" },
				{ content: {}, id: "y", type: "text" }
			]);
			const wrapper = mount({ max: 4 });
			clipboard(wrapper);
			await wrapper.vm.paste(new Event("paste"), 0);
			expect(ids(lastInput(wrapper))).toStrictEqual(["x", "a", "b", "c"]);
		});
	});

	describe("pasteboard()", () => {
		it("opens the pasteboard", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.pasteboard();
			expect(dialog($panel)).toStrictEqual({
				component: "k-block-pasteboard",
				on: { paste: wrapper.vm.paste }
			});
		});
	});

	describe("prevNext()", () => {
		// the refs only exist after the first render,
		// selecting a block renders them into the neighbours
		it("passes the neighbours to each block once selected", async () => {
			const wrapper = mount();
			selection(wrapper, ["a"]);
			await wrapper.vm.$nextTick();
			const [first, second] = items(wrapper);
			expect(second.props("prev")).toBe(first.vm);
			expect(first.props("prev")).toBeUndefined();
		});
	});

	describe("remove()", () => {
		it("removes and deselects the block", () => {
			const wrapper = mount();
			selection(wrapper, ["b"]);
			wrapper.vm.remove(blocks()[1]);
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "c"]);
			expect(wrapper.vm.selected).toStrictEqual([]);
		});

		it("removes all blocks once confirmed", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.removeAll();
			dialog($panel).on.submit();
			expect(lastInput(wrapper)).toStrictEqual([]);
			expect($panel.dialog.close).toHaveBeenCalledOnce();
		});

		it("removes the selected blocks once confirmed", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			selection(wrapper, ["a", "c"]);
			wrapper.vm.removeSelected();
			expect(dialog($panel).props.text).toBe(
				"field.blocks.delete.confirm.selected"
			);
			dialog($panel).on.submit();
			expect(ids(lastInput(wrapper))).toStrictEqual(["b"]);
			expect(wrapper.vm.selected).toStrictEqual([]);
		});
	});

	describe("select()", () => {
		it("adds the block to the selection once", () => {
			const wrapper = mount();
			wrapper.vm.select(blocks()[0]);
			wrapper.vm.select(blocks()[0]);
			expect(wrapper.vm.selected).toStrictEqual(["a"]);
		});

		it("selects all blocks", () => {
			const wrapper = mount();
			wrapper.vm.selectAll();
			expect(wrapper.vm.selected).toStrictEqual(["a", "b", "c"]);
		});

		it("extends the selection down and up", () => {
			const wrapper = mount();
			selection(wrapper, ["b"]);
			wrapper.vm.selectDown();
			wrapper.vm.selectUp();
			expect(wrapper.vm.selected).toStrictEqual(["b", "c", "a"]);
		});

		it("marks the last selected block", async () => {
			const wrapper = mount();
			selection(wrapper, ["a", "b"]);
			await wrapper.vm.$nextTick();
			expect(
				items(wrapper).map((item) => item.props("isLastSelected"))
			).toStrictEqual([false, true, false]);
			expect(items(wrapper)[0].props("isBatched")).toBe(true);
		});
	});

	describe("show()", () => {
		it("shows the block", () => {
			const value = blocks();
			value[0].isHidden = true;
			const wrapper = mount({ value });
			wrapper.vm.show(value[0]);
			expect(lastInput(wrapper)?.[0].isHidden).toBe(false);
		});
	});

	describe("sort()", () => {
		it("moves the block and keeps the focus on it", async () => {
			const wrapper = mount();
			await wrapper.vm.sort(blocks()[0], 0, 1);
			expect(ids(lastInput(wrapper))).toStrictEqual(["b", "a", "c"]);
			expect(BlockStub.methods.focus).toHaveBeenCalledOnce();
		});

		it("keeps the first block at the top", async () => {
			const wrapper = mount();
			await wrapper.vm.sort(blocks()[0], 0, -1);
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("split()", () => {
		it("splits the block into two", async () => {
			const wrapper = mount();
			await wrapper.vm.split(blocks()[0], 0, [{ text: "A1" }, { text: "A2" }]);
			const list = lastInput(wrapper);
			expect(ids(list)).toStrictEqual(["a", "new", "b", "c"]);
			expect(list?.[0].content).toStrictEqual({ text: "A1" });
			expect(list?.[1].content).toStrictEqual({ text: "A2" });
		});
	});

	describe("update()", () => {
		it("updates the content of the block", () => {
			const wrapper = mount();
			wrapper.vm.update(blocks()[1], { text: "B2" });
			expect(lastInput(wrapper)?.[1].content).toStrictEqual({ text: "B2" });
		});
	});

	// events
	describe("block events", () => {
		it("opens and closes the editing state", () => {
			const wrapper = mount();
			items(wrapper)[0].vm.$emit("open");
			expect(wrapper.vm.isEditing).toBe(true);
			items(wrapper)[0].vm.$emit("close");
			expect(wrapper.vm.isEditing).toBe(false);
		});

		it("removes and duplicates the block", () => {
			const wrapper = mount();
			items(wrapper)[0].vm.$emit("duplicate");
			expect(lastInput(wrapper)).toHaveLength(4);
			items(wrapper)[0].vm.$emit("remove");
			expect(lastInput(wrapper)).toHaveLength(3);
		});

		it("sorts the block down and up", async () => {
			const wrapper = mount();
			items(wrapper)[1].vm.$emit("sort-down");
			await flushPromises();
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "c", "b"]);
		});

		it("updates the content of the block", () => {
			const wrapper = mount();
			items(wrapper)[2].vm.$emit("update", { alt: "C2" });
			expect(lastInput(wrapper)?.[2].content).toStrictEqual({ alt: "C2" });
		});

		it("appends a new block", async () => {
			const wrapper = mount();
			items(wrapper)[0].vm.$emit("append", "heading");
			await flushPromises();
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "new", "b", "c"]);
		});
	});

	describe("click event", () => {
		it("chooses a block with a click on the empty state", () => {
			const $panel = panel();
			const wrapper = mount({ value: [] }, {}, $panel);
			wrapper.find(".k-blocks-empty").trigger("click");
			expect(dialog($panel).component).toBe("k-block-selector");
		});
	});

	describe("sort event", () => {
		it("emits the blocks after dragging", () => {
			const wrapper = mount();
			wrapper.findComponent(Draggable).vm.$emit("sort");
			expect(ids(lastInput(wrapper))).toStrictEqual(["a", "b", "c"]);
		});
	});
});
