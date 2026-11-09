import { describe, expect, it, onTestFinished, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import type { NodeSpec } from "prosemirror-model";
import { NodeSelection } from "prosemirror-state";
import { nextTick } from "vue";
import Editor from "./Editor";
import type Extension from "./Extension";
import { Bold, Clear, Italic, Link } from "./Marks";
import Node from "./Node";
import {
	BulletList,
	Heading,
	ListDoc,
	ListItem,
	OrderedList,
	Quote
} from "./Nodes";
import WriterToolbar from "./Toolbar.vue";

type Button = {
	click: (event?: Event) => void;
	current: boolean;
	disabled?: boolean;
	dropdown?: Button[];
	icon: string;
};

// receives the buttons like the globally registered k-toolbar
const Toolbar = {
	props: ["buttons", "theme"],
	template: "<nav />"
};

// block node of a plugin with its own command
class Callout extends Node {
	get button() {
		return { command: "toggleCallout", icon: "alert", label: "Callout" };
	}

	get name() {
		return "callout";
	}

	get schema(): NodeSpec {
		return { content: "inline*", group: "block", toDOM: () => ["aside", 0] };
	}
}

// inline node of a plugin, listed next to the marks
class Mention extends Node {
	get button() {
		return {
			command: "insertMention",
			icon: "user",
			inline: true,
			label: "Mention"
		};
	}

	get name() {
		return "mention";
	}

	get schema(): NodeSpec {
		return { atom: true, group: "inline", inline: true, toDOM: () => ["span"] };
	}
}

function createEditor(
	content: string | Record<string, unknown> = "",
	extensions: Extension[] = [
		new Bold(),
		new Italic(),
		new Link(),
		new Clear(),
		new Heading({ levels: [1, 2] }),
		new BulletList(),
		new OrderedList(),
		new ListItem(),
		new Quote()
	]
): Editor {
	const element = document.createElement("div");
	document.body.appendChild(element);

	const editor = new Editor({ content, element, extensions });
	onTestFinished(() => editor.destroy());

	return editor;
}

function mount(props: Record<string, unknown> = {}) {
	return vueMount(WriterToolbar, {
		props: { editor: createEditor(), inline: false, ...props },
		global: { components: { "k-toolbar": Toolbar } }
	});
}

// the buttons k-toolbar receives
function buttons(wrapper: ReturnType<typeof mount>): (Button | "|")[] {
	return wrapper.findComponent(Toolbar).props("buttons");
}

function dropdown(wrapper: ReturnType<typeof mount>): (Button | "-")[] {
	return (buttons(wrapper)[0] as Button).dropdown!;
}

function icons(buttons: (Button | string)[]): string[] {
	return buttons.map((button) =>
		typeof button === "string" ? button : button.icon
	);
}

// adds the panel menu the inline toolbar has to stay clear of
function menu(width: number) {
	const element = document.createElement("div");
	element.className = "k-panel-menu";
	document.body.appendChild(element);

	const rect = vi
		.spyOn(element, "getBoundingClientRect")
		.mockReturnValue({ width } as DOMRect);
	onTestFinished(() => rect.mockRestore());
}

// stubs the rectangles of the toolbar, the editor and the selection
function rects(
	wrapper: ReturnType<typeof mount>,
	editor: Editor,
	sizes: {
		editor: Partial<DOMRect>;
		selection: { left: number; right: number; top: number; bottom: number };
		toolbar: Partial<DOMRect>;
	}
) {
	const { left, right, top, bottom } = sizes.selection;
	const spies = [
		vi
			.spyOn(wrapper.element, "getBoundingClientRect")
			.mockReturnValue(sizes.toolbar as DOMRect),
		vi
			.spyOn(editor.element!, "getBoundingClientRect")
			.mockReturnValue(sizes.editor as DOMRect),
		vi
			.spyOn(editor.view!, "coordsAtPos")
			.mockImplementation((pos) =>
				pos === editor.selection.from
					? { left, right: left, top, bottom: top }
					: { left: right, right, top: bottom, bottom }
			)
	];

	onTestFinished(() => spies.forEach((spy) => spy.mockRestore()));
}

describe("k-writer-toolbar", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) =>
			vueMount(WriterToolbar, {
				props: { editor: createEditor(), inline: false },
				attrs
			});

		it.rendersAs(component, "K-TOOLBAR", "k-writer-toolbar");
		it.acceptsClass(component);
		it.acceptsStyle(component);
	});

	// props
	describe("inline prop", () => {
		it("hides the toolbar until it is opened", async () => {
			const wrapper = mount({ inline: true });
			expect(wrapper.findComponent(Toolbar).exists()).toBe(false);

			wrapper.vm.open();
			await nextTick();

			expect(wrapper.findComponent(Toolbar).exists()).toBe(true);
			expect(wrapper.attributes("data-inline")).toBe("true");
		});

		it("shows the toolbar right away when false", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Toolbar).exists()).toBe(true);
			expect(wrapper.attributes("data-inline")).toBe("false");
		});
	});

	describe("marks prop", () => {
		it("shows the default marks with dividers", () => {
			const wrapper = mount({ nodes: false });
			expect(icons(buttons(wrapper))).toEqual([
				"bold",
				"italic",
				"|",
				"url",
				"|",
				"clear"
			]);
		});

		it("shows all marks of the editor when true", () => {
			const wrapper = mount({ marks: true, nodes: false });
			expect(icons(buttons(wrapper))).toEqual([
				"bold",
				"italic",
				"url",
				"clear"
			]);
		});

		it("shows the requested marks only", () => {
			const wrapper = mount({
				marks: ["link", "|", "bold", "underline"],
				nodes: false
			});
			expect(icons(buttons(wrapper))).toEqual(["url", "|", "bold"]);
		});

		it("shows no marks when false", () => {
			const wrapper = mount({ marks: false, nodes: false });
			expect(buttons(wrapper)).toEqual([]);
		});
	});

	describe("nodes prop", () => {
		it("lists all block nodes in the dropdown by default", () => {
			const wrapper = mount();
			expect(icons(dropdown(wrapper))).toEqual([
				"paragraph",
				"-",
				"h1",
				"h2",
				"-",
				"list-bullet",
				"list-numbers",
				"-",
				"quote"
			]);
		});

		it("lists the requested block nodes in their order", () => {
			const wrapper = mount({ nodes: ["quote", "h1", "unknown", "h2"] });
			expect(icons(dropdown(wrapper))).toEqual(["quote", "h1", "h2"]);
		});

		it("shows no dropdown when false", () => {
			const wrapper = mount({ nodes: false });
			expect(icons(buttons(wrapper))).toEqual([
				"bold",
				"italic",
				"|",
				"url",
				"|",
				"clear"
			]);
		});

		it("adds inline nodes in front of the marks", () => {
			const editor = createEditor("", [
				new Bold(),
				new Heading({ levels: [1, 2] }),
				new Mention()
			]);

			const wrapper = mount({ editor, marks: ["bold"] });
			expect(icons(buttons(wrapper))).toEqual([
				"paragraph",
				"|",
				"user",
				"|",
				"bold"
			]);
			expect(icons(dropdown(wrapper))).not.toContain("user");
		});

		it("adds requested inline nodes", () => {
			const editor = createEditor("", [
				new Bold(),
				new Heading({ levels: [1, 2] }),
				new Mention()
			]);

			const wrapper = mount({ editor, marks: ["bold"], nodes: ["mention"] });
			expect(icons(buttons(wrapper))).toEqual(["user", "|", "bold"]);
		});

		it("leaves out inline nodes that are not requested", () => {
			const editor = createEditor("", [
				new Bold(),
				new Heading({ levels: [1, 2] }),
				new Mention()
			]);

			const wrapper = mount({ editor, marks: ["bold"], nodes: ["h1", "h2"] });
			expect(icons(buttons(wrapper))).toEqual(["title", "|", "bold"]);
		});
	});

	// computed
	describe("activeDropdownEntry computed", () => {
		it("shows the icon of the active block node", async () => {
			const editor = createEditor("<h2>Title</h2>");
			const wrapper = mount({ editor });

			expect(buttons(wrapper)[0]).toMatchObject({ current: true, icon: "h2" });

			editor.setContent("<h1>Title</h1>");
			editor.setSelection(2);
			await nextTick();

			expect(buttons(wrapper)[0]).toMatchObject({ current: true, icon: "h1" });
		});

		it("prefers a list over the paragraph inside it", () => {
			const editor = createEditor("<ul><li><p>Item</p></li></ul>");
			editor.setSelection(3);

			const wrapper = mount({ editor });
			expect(buttons(wrapper)[0]).toMatchObject({
				current: true,
				icon: "list-bullet"
			});
		});

		it("falls back to the title icon without an active entry", () => {
			const wrapper = mount({ nodes: ["bulletList", "orderedList"] });
			expect(buttons(wrapper)[0]).toMatchObject({
				current: false,
				icon: "title"
			});
		});
	});

	describe("buttons computed", () => {
		it("separates the dropdown from the inline buttons", () => {
			const wrapper = mount();
			expect(icons(buttons(wrapper))).toEqual([
				"paragraph",
				"|",
				"bold",
				"italic",
				"|",
				"url",
				"|",
				"clear"
			]);
		});

		it("drops the divider without inline buttons", () => {
			const wrapper = mount({ marks: false });
			expect(icons(buttons(wrapper))).toEqual(["paragraph"]);
		});

		it("drops the dropdown for a single block node", () => {
			const wrapper = mount({ nodes: ["paragraph"] });
			expect(icons(buttons(wrapper))).toEqual([
				"bold",
				"italic",
				"|",
				"url",
				"|",
				"clear"
			]);
		});
	});

	describe("dropdown computed", () => {
		it("does not end with a separator", () => {
			const wrapper = mount({
				nodes: ["paragraph", "bulletList", "orderedList"]
			});
			expect(icons(dropdown(wrapper))).toEqual([
				"paragraph",
				"-",
				"list-bullet",
				"list-numbers"
			]);
		});
	});

	describe("dropdownEntries computed", () => {
		it("drops the paragraph when the document only allows lists", () => {
			const editor = createEditor("<ul><li><p>Item</p></li></ul>", [
				new ListDoc({ nodes: ["bulletList", "orderedList"] }),
				new BulletList(),
				new OrderedList(),
				new ListItem()
			]);

			const wrapper = mount({ editor, marks: false });
			expect(icons(dropdown(wrapper))).toEqual(["list-bullet", "list-numbers"]);
		});
	});

	describe("positions computed", () => {
		it("places the inline toolbar", async () => {
			const wrapper = mount({ inline: true });
			await wrapper.setData({ isOpen: true, position: { x: 10, y: 20 } });

			expect(wrapper.attributes("style")).toBe("top: 20px; left: 10px;");
		});

		it("leaves the fixed toolbar in place", async () => {
			const wrapper = mount({ inline: false });
			await wrapper.setData({ position: { x: 10, y: 20 } });

			expect(wrapper.attributes("style")).toBeUndefined();
		});
	});

	describe("theme computed", () => {
		it("is dark for the inline toolbar", async () => {
			const wrapper = mount({ inline: true });
			await wrapper.setData({ isOpen: true });

			expect(wrapper.findComponent(Toolbar).props("theme")).toBe("dark");
		});

		it("is light for the fixed toolbar", () => {
			const wrapper = mount({ inline: false });
			expect(wrapper.findComponent(Toolbar).props("theme")).toBe("light");
		});
	});

	// methods
	describe("close()", () => {
		it("closes the toolbar", async () => {
			const wrapper = mount({ inline: true });
			await wrapper.setData({ isOpen: true });

			// the writer closes it without an event
			(wrapper.vm.close as () => void)();
			await nextTick();

			expect(wrapper.findComponent(Toolbar).exists()).toBe(false);
		});

		it("stays open while the focus moves into the toolbar", async () => {
			const wrapper = mount({ inline: true });
			await wrapper.setData({ isOpen: true });

			wrapper.vm.close(
				new FocusEvent("blur", { relatedTarget: wrapper.element })
			);
			await nextTick();

			expect(wrapper.findComponent(Toolbar).exists()).toBe(true);
		});

		it("closes when the focus leaves the toolbar", async () => {
			const wrapper = mount({ inline: true });
			await wrapper.setData({ isOpen: true });

			wrapper.vm.close(
				new FocusEvent("blur", { relatedTarget: document.body })
			);
			await nextTick();

			expect(wrapper.findComponent(Toolbar).exists()).toBe(false);
		});
	});

	describe("dropdownButton()", () => {
		it("marks the active entry as current", () => {
			const editor = createEditor("<h2>Title</h2>");
			const wrapper = mount({ editor });

			const current = dropdown(wrapper).map(
				(button) => button !== "-" && button.current
			);
			expect(current).toEqual([
				false,
				false,
				false,
				true,
				false,
				false,
				false,
				false,
				false
			]);
		});

		it("disables entries that cannot replace the active node", () => {
			const editor = createEditor("<ul><li><p>Item</p></li></ul>");
			editor.setSelection(3);

			const wrapper = mount({ editor });
			const disabled = Object.fromEntries(
				dropdown(wrapper)
					.filter((button) => button !== "-")
					.map((button) => [button.icon, button.disabled])
			);

			expect(disabled).toEqual({
				paragraph: false,
				h1: true,
				h2: true,
				"list-bullet": false,
				"list-numbers": false,
				quote: true
			});
		});

		it("disables nothing when the active node has no restrictions", () => {
			const wrapper = mount();
			const disabled = dropdown(wrapper)
				.filter((button) => button !== "-")
				.map((button) => button.disabled);

			expect(disabled).not.toContain(true);
		});
	});

	describe("inlineButton()", () => {
		it("marks active marks as current", () => {
			const editor = createEditor("<p><strong>Bold</strong></p>");
			editor.setSelection(2);

			const wrapper = mount({ editor, nodes: false });
			const current = buttons(wrapper).map(
				(button) => button !== "|" && button.current
			);
			expect(current).toEqual([true, false, false, false, false, false]);
		});

		it("marks the selected inline node as current", async () => {
			const editor = createEditor(
				{
					type: "doc",
					content: [
						{
							type: "paragraph",
							content: [{ type: "mention" }, { type: "text", text: "!" }]
						}
					]
				},
				[new Bold(), new Mention()]
			);

			const wrapper = mount({ editor, marks: ["bold"] });
			const mention = () => buttons(wrapper)[0] as Button;
			expect(mention().current).toBe(false);

			editor.view!.dispatch(
				editor.state!.tr.setSelection(
					NodeSelection.create(editor.state!.doc, 1)
				)
			);
			await nextTick();

			expect(mention().current).toBe(true);
		});
	});

	describe("open()", () => {
		it("stays closed without any buttons", async () => {
			const wrapper = mount({ inline: true, marks: false, nodes: false });

			wrapper.vm.open();
			await nextTick();

			expect(wrapper.findComponent(Toolbar).exists()).toBe(false);
		});

		it("does not position the fixed toolbar", async () => {
			const editor = createEditor("<p>Text</p>");
			editor.setSelection(1, 5);
			const wrapper = mount({ editor });
			rects(wrapper, editor, {
				editor: { x: 0, y: 0, width: 600 },
				selection: { left: 300, right: 300, top: 200, bottom: 220 },
				toolbar: { height: 40, width: 200 }
			});

			wrapper.vm.open();
			await nextTick();

			expect(wrapper.vm.position).toEqual({ x: 0, y: 0 });
		});

		it("positions the inline toolbar", async () => {
			const editor = createEditor("<p>Text</p>");
			editor.setSelection(1, 5);
			const wrapper = mount({ editor, inline: true });
			await wrapper.setData({ isOpen: true });

			rects(wrapper, editor, {
				editor: { x: 100, y: 100, width: 600 },
				selection: { left: 300, right: 400, top: 200, bottom: 220 },
				toolbar: { height: 40, width: 200 }
			});

			wrapper.vm.open();
			await nextTick();

			expect(wrapper.vm.position).toEqual({ x: 150, y: 55 });
		});
	});

	describe("setPosition()", () => {
		async function position(
			rect: Parameters<typeof rects>[2]
		): Promise<{ x: number; y: number }> {
			const editor = createEditor("<p>Text</p>");
			editor.setSelection(1, 5);
			const wrapper = mount({ editor, inline: true });
			await wrapper.setData({ isOpen: true });

			rects(wrapper, editor, rect);
			wrapper.vm.setPosition();

			return wrapper.vm.position;
		}

		it("centers the toolbar above the selection", async () => {
			const result = await position({
				editor: { x: 100, y: 100, width: 600 },
				selection: { left: 300, right: 400, top: 200, bottom: 220 },
				toolbar: { height: 40, width: 200 }
			});

			expect(result).toEqual({ x: 150, y: 55 });
		});

		it("keeps the toolbar inside the left edge of the editor", async () => {
			const result = await position({
				editor: { x: 100, y: 100, width: 600 },
				selection: { left: 120, right: 140, top: 200, bottom: 220 },
				toolbar: { height: 40, width: 200 }
			});

			expect(result.x).toBe(0);
		});

		it("keeps the toolbar inside the right edge of the editor", async () => {
			const result = await position({
				editor: { x: 100, y: 100, width: 600 },
				selection: { left: 660, right: 680, top: 200, bottom: 220 },
				toolbar: { height: 40, width: 200 }
			});

			expect(result.x).toBe(400);
		});

		it("keeps a wider toolbar clear of the panel menu", async () => {
			menu(200);

			// centered, the toolbar would start at 150px
			const result = await position({
				editor: { x: 250, y: 100, width: 100 },
				selection: { left: 290, right: 310, top: 200, bottom: 220 },
				toolbar: { height: 40, width: 300 }
			});

			expect(result.x).toBe(-30);
		});

		it("keeps a wider toolbar inside the viewport", async () => {
			menu(100);

			const result = await position({
				editor: { x: window.innerWidth - 100, y: 100, width: 100 },
				selection: {
					left: window.innerWidth - 60,
					right: window.innerWidth - 40,
					top: 200,
					bottom: 220
				},
				toolbar: { height: 40, width: 300 }
			});

			// ends 20px before the right edge of the viewport
			expect(result.x).toBe(-220);
		});

		it("leaves a wider toolbar in place when it fits the viewport", async () => {
			menu(100);

			const result = await position({
				editor: { x: 300, y: 100, width: 100 },
				selection: { left: 340, right: 360, top: 200, bottom: 220 },
				toolbar: { height: 40, width: 300 }
			});

			expect(result.x).toBe(-100);
		});
	});

	// events
	describe("command event", () => {
		it("is emitted with the command of a dropdown entry", () => {
			const editor = createEditor("", [
				new Heading({ levels: [1, 2] }),
				new Callout()
			]);
			const wrapper = mount({ editor });
			const [paragraph, , h1, , , callout] = dropdown(wrapper) as Button[];

			paragraph.click();
			h1.click();
			callout.click();

			expect(wrapper.emitted("command")).toEqual([
				["paragraph"],
				["h1"],
				["toggleCallout"]
			]);
		});

		it("is emitted with the command of an inline button and the event", () => {
			const editor = createEditor("", [
				new Bold(),
				new Heading({ levels: [1, 2] }),
				new Mention()
			]);
			const wrapper = mount({ editor, marks: ["bold"], nodes: ["mention"] });
			const [mention, , bold] = buttons(wrapper) as Button[];
			const event = new MouseEvent("click");

			mention.click(event);
			bold.click(event);

			expect(wrapper.emitted("command")).toEqual([
				["insertMention", event],
				["bold", event]
			]);
		});
	});
});
