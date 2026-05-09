import {
	afterEach,
	beforeAll,
	beforeEach,
	describe,
	expect,
	it,
	vi
} from "vitest";
import Editor from "./Editor";
import { Bold, Link } from "./Marks";
import { Heading } from "./Nodes";

function createEditor(options: Record<string, unknown> = {}): Editor {
	const element = document.createElement("div");
	document.body.appendChild(element);
	return new Editor({ element, ...options });
}

describe("Editor", () => {
	beforeAll(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	let editor: Editor;

	beforeEach(() => {
		editor = createEditor();
	});

	afterEach(() => {
		editor.destroy();
		document.body.innerHTML = "";
	});

	describe("constructor", () => {
		it("initializes with default options", () => {
			expect(editor.options.autofocus).toBe(false);
			expect(editor.options.content).toBe("");
			expect(editor.options.editable).toBe(true);
			expect(editor.options.inline).toBe(false);
			expect(editor.options.useBuiltInExtensions).toBe(true);
		});

		it("merges provided options with defaults", () => {
			const e = createEditor({ editable: false, content: "<p>hi</p>" });
			expect(e.options.content).toBe("<p>hi</p>");
			expect(e.options.editable).toBe(false);
			expect(editor.options.inline).toBe(false);
			e.destroy();
		});

		it("sets focused to false initially", () => {
			expect(editor.focused).toBe(false);
		});

		it("emits the init event on creation", () => {
			const onInit = vi.fn();
			const element = document.createElement("div");
			document.body.appendChild(element);
			const e = new Editor({ element, events: { init: onInit } });
			expect(onInit).toHaveBeenCalledOnce();
			e.destroy();
		});
	});

	describe("activeMarkAttrs", () => {
		it("returns an empty object when no marks are active", () => {
			expect(editor.activeMarkAttrs).toEqual({});
		});

		it("includes the attrs of a mark active at the cursor", () => {
			const e = createEditor({ extensions: [new Link()] });
			e.setContent('<p><a href="https://example.com">hello</a></p>');
			e.setSelection(2, 2);
			expect(e.activeMarkAttrs.link).toEqual({
				href: "https://example.com",
				target: null,
				title: null
			});
			e.destroy();
		});
	});

	describe("activeMarks", () => {
		it("returns an empty array when no marks are active", () => {
			expect(editor.activeMarks).toEqual([]);
		});

		it("includes the name of a mark active at the cursor", () => {
			const e = createEditor({ extensions: [new Bold()] });
			e.setContent("<p><strong>hello</strong></p>");
			e.setSelection(2, 2);
			expect(e.activeMarks).toContain("bold");
			e.destroy();
		});
	});

	describe("activeNodeAttrs", () => {
		it("returns attrs for the node type at the cursor", () => {
			const e = createEditor({ extensions: [new Heading()] });
			e.setContent("<h2>hello</h2>");
			e.setSelection(2, 2);
			expect(e.activeNodeAttrs.heading).toEqual({ level: 2 });
			e.destroy();
		});
	});

	describe("activeNodes", () => {
		it("includes the node type at the cursor position", () => {
			const e = createEditor({ extensions: [new Heading()] });
			e.setContent("<h2>hello</h2>");
			e.setSelection(2, 2);
			expect(e.activeNodes).toContain("heading");
			e.destroy();
		});
	});

	describe("blur", () => {
		it("removes focus from the editor DOM element", () => {
			editor.view!.dom.focus();
			expect(document.activeElement).toBe(editor.view!.dom);
			editor.blur();
			expect(document.activeElement).not.toBe(editor.view!.dom);
		});
	});

	describe("buttons", () => {
		it("returns an empty object when no mark extensions are registered", () => {
			expect(editor.buttons("mark")).toEqual({});
		});

		it("returns a button entry for each registered mark extension", () => {
			const e = createEditor({ extensions: [new Bold()] });
			expect(e.buttons("mark")).toMatchObject({
				bold: { name: "bold" }
			});
			e.destroy();
		});

		it("returns a button entry for each registered node extension", () => {
			expect(editor.buttons("node")).toMatchObject({
				paragraph: { name: "paragraph" }
			});
		});

		it("returns one button entry per heading level", () => {
			const e = createEditor({ extensions: [new Heading({ levels: [1, 2] })] });
			expect(e.buttons("node")).toMatchObject({
				h1: { id: "h1" },
				h2: { id: "h2" }
			});
			e.destroy();
		});
	});

	describe("clearContent", () => {
		it("resets the document to empty", () => {
			editor.setContent("<p>hello</p>");
			expect(editor.isEmpty()).toBe(false);
			editor.clearContent();
			expect(editor.isEmpty()).toBe(true);
		});

		it("emits an update event when emitUpdate is true", () => {
			editor.setContent("<p>hello</p>");
			const onUpdate = vi.fn();
			editor.on("update", onUpdate);
			expect(onUpdate).not.toHaveBeenCalledOnce();
			editor.clearContent(true);
			expect(onUpdate).toHaveBeenCalledOnce();
		});
	});

	describe("command", () => {
		it("calls the named command with forwarded arguments", () => {
			const command = vi.fn();
			editor.commands.testCmd = command;
			editor.command("testCmd", "arg1");
			expect(command).toHaveBeenCalledWith("arg1");
		});

		it("does not throw when the command does not exist", () => {
			expect(() => editor.command("foo")).not.toThrow();
		});
	});

	describe("createDocument", () => {
		it("returns an empty document when content is null", () => {
			const doc = editor.createDocument(null);
			expect(doc.textContent).toBe("");
		});

		it("parses a JSON object into a document", () => {
			const json = {
				type: "doc",
				content: [
					{
						type: "paragraph",
						content: [{ type: "text", text: "hello" }]
					}
				]
			};
			const doc = editor.createDocument(json);
			expect(doc.textContent).toBe("hello");
		});

		it("parses an HTML string into a document", () => {
			const doc = editor.createDocument("<p>hello</p>");
			expect(doc.textContent).toBe("hello");
		});

		it("returns an empty document for invalid JSON and logs a warning", () => {
			const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
			const doc = editor.createDocument({ type: "invalid_type" });
			expect(doc.textContent).toBe("");
			expect(warn).toHaveBeenCalled();
			warn.mockRestore();
		});

		it("returns false for non-string, non-object content", () => {
			expect(editor.createDocument(42)).toBe(false);
		});
	});

	describe("destroy", () => {
		it("removes the editor DOM element from the document", () => {
			const e = createEditor();
			const dom = e.view!.dom;
			expect(document.body.contains(dom)).toBe(true);
			e.destroy();
			expect(document.body.contains(dom)).toBe(false);
			expect(e.view).toBeNull();
		});

		it("does not throw when called on an already-destroyed view", () => {
			const e = createEditor();
			e.destroy();
			expect(() => e.destroy()).not.toThrow();
		});
	});

	describe("dispatchTransaction", () => {
		it("emits a transaction event on every transaction", () => {
			const onTransaction = vi.fn();
			editor.on("transaction", onTransaction);
			editor.insertText("x");
			expect(onTransaction).toHaveBeenCalledOnce();
		});

		it("emits an update event when the document changes", () => {
			const onUpdate = vi.fn();
			editor.on("update", onUpdate);
			editor.insertText("x");
			expect(onUpdate).toHaveBeenCalledOnce();
		});

		it("emits a select event when the selection is non-empty", () => {
			const onSelect = vi.fn();
			editor.on("select", onSelect);
			editor.setContent("<p>hello</p>");
			editor.setSelection(1, 4);
			expect(onSelect).toHaveBeenCalledOnce();
		});

		it("emits a deselect event when the selection is empty (cursor only)", () => {
			editor.setContent("<p>hello</p>");
			const onDeselect = vi.fn();
			editor.on("deselect", onDeselect);
			editor.setSelection(2, 2);
			expect(onDeselect).toHaveBeenCalledOnce();
		});

		it("does not emit an update event for a selection-only change", () => {
			editor.setContent("<p>hello</p>");
			const onUpdate = vi.fn();
			editor.on("update", onUpdate);
			editor.setSelection(1, 3);
			expect(onUpdate).not.toHaveBeenCalled();
		});
	});

	describe("focus", () => {
		beforeEach(() => {
			vi.useFakeTimers();
		});

		afterEach(() => {
			vi.useRealTimers();
		});

		it("focuses the editor DOM element", () => {
			expect(document.activeElement).not.toBe(editor.view!.dom);
			editor.focus();
			vi.runAllTimers();
			expect(document.activeElement).toBe(editor.view!.dom);
		});

		it("moves the cursor to the start of the document", () => {
			editor.setContent("<p>hello</p>");
			editor.setSelection(3, 3);
			editor.focus("start");
			vi.runAllTimers();
			expect(document.activeElement).toBe(editor.view!.dom);
			expect(editor.selection.head).toBe(editor.selectionAtStart.head);
		});

		it("moves the cursor to the end of the document", () => {
			editor.setContent("<p>hello</p>");
			editor.focus("end");
			vi.runAllTimers();
			expect(document.activeElement).toBe(editor.view!.dom);
			expect(editor.selection.head).toBe(editor.selectionAtEnd.head);
		});

		it("moves the cursor to a specific position", () => {
			editor.setContent("<p>hello</p>");
			editor.focus(3);
			vi.runAllTimers();
			expect(document.activeElement).toBe(editor.view!.dom);
			expect(editor.selection.from).toBe(3);
		});

		it("is a no-op when position is false", () => {
			editor.setContent("<p>hello</p>");
			editor.setSelection(2, 2);
			editor.focus(false);
			vi.runAllTimers();
			expect(document.activeElement).not.toBe(editor.view!.dom);
			expect(editor.selection.from).toBe(2);
		});
	});

	describe("getHTML", () => {
		it("serializes a paragraph with text to HTML", () => {
			const e = createEditor({ extensions: [new Bold()] });
			e.setContent("<p>hello <strong>world</strong></p>");
			expect(e.getHTML()).toBe("<p>hello <strong>world</strong></p>");
			e.destroy();
		});

		it("returns only the inner paragraph content in inline mode", () => {
			const e = createEditor({ inline: true });
			e.setContent("<p>hello</p>");
			expect(e.getHTML()).toBe("hello");
			e.destroy();
		});
	});

	describe("getHTMLSelectionToEnd", () => {
		it("returns HTML from the cursor to the document end", () => {
			editor.setContent("<p>hello world</p>");
			editor.setSelection(6, 6);
			expect(editor.getHTMLSelectionToEnd()).toBe("<p> world</p>");
		});
	});

	describe("getHTMLStartToSelection", () => {
		it("returns HTML from the document start to the cursor", () => {
			editor.setContent("<p>hello world</p>");
			editor.setSelection(6, 6);
			expect(editor.getHTMLStartToSelection()).toBe("<p>hello</p>");
		});
	});

	describe("getHTMLStartToSelectionToEnd", () => {
		it("returns a two-element array splitting the document at the cursor", () => {
			editor.setContent("<p>hello world</p>");
			editor.setSelection(6, 6);
			const [before, after] = editor.getHTMLStartToSelectionToEnd();
			expect(before).toBe("<p>hello</p>");
			expect(after).toBe("<p> world</p>");
		});
	});

	describe("getJSON", () => {
		it("returns a JSON object with type 'doc'", () => {
			const json = editor.getJSON();
			expect(json.type).toBe("doc");
			expect(Array.isArray(json.content)).toBe(true);
		});

		it("includes text content in the JSON representation", () => {
			editor.setContent("<p>hello</p>");
			expect(JSON.stringify(editor.getJSON())).toContain("hello");
		});
	});

	describe("getMarkAttrs", () => {
		it("returns the attrs of an active mark at the cursor", () => {
			const e = createEditor({ extensions: [new Link()] });
			e.setContent('<p><a href="https://example.com">hello</a></p>');
			e.setSelection(2, 2);
			expect(e.getMarkAttrs("link")).toEqual({
				href: "https://example.com",
				target: null,
				title: null
			});
			e.destroy();
		});

		it("returns empty attrs for a known mark that is not active", () => {
			const e = createEditor({ extensions: [new Link()] });
			e.setContent("<p>hello</p>");
			e.setSelection(2, 2);
			expect(e.getMarkAttrs("link")).toEqual({});
			e.destroy();
		});

		it("returns undefined for a mark not present in the schema", () => {
			expect(editor.getMarkAttrs("nonexistent")).toBeUndefined();
		});
	});

	describe("insertText", () => {
		it("inserts text at the current cursor position", () => {
			editor.setContent("<p>hello</p>");
			const end = editor.selectionAtEnd.head;
			editor.setSelection(end, end);
			editor.insertText(" world!");
			expect(editor.getHTML()).toBe("<p>hello world!</p>");
		});

		it("selects the inserted text when selected is true", () => {
			editor.setContent("<p>hello</p>");
			editor.insertText("world", true);
			expect(editor.selection.to - editor.selection.from).toBe("world".length);
		});
	});

	describe("isEditable", () => {
		it("returns true by default", () => {
			expect(editor.isEditable()).toBe(true);
		});

		it("returns false when the editable option is false", () => {
			const e = createEditor({ editable: false });
			expect(e.isEditable()).toBe(false);
			e.destroy();
		});
	});

	describe("isEmpty", () => {
		it("returns true for an empty document", () => {
			expect(editor.isEmpty()).toBe(true);
		});

		it("returns false when the document has text", () => {
			editor.setContent("<p>hello</p>");
			expect(editor.isEmpty()).toBe(false);
		});
	});

	describe("removeMark", () => {
		it("removes an active mark from the current selection", () => {
			const e = createEditor({ extensions: [new Bold()] });
			e.setContent("<p><strong>hello</strong></p>");
			e.setSelection(1, 6);
			expect(e.activeMarks).toContain("bold");
			e.removeMark("bold");
			expect(e.activeMarks).not.toContain("bold");
			expect(e.getHTML()).toBe("<p>hello</p>");
			e.destroy();
		});

		it("does not throw for an unknown mark name", () => {
			expect(() => editor.removeMark("nonexistent")).not.toThrow();
		});
	});

	describe("selection", () => {
		it("is collapsed by default", () => {
			expect(editor.selection.from).toBe(editor.selection.to);
		});

		it("reflects the current cursor position", () => {
			editor.setContent("<p>hello</p>");
			editor.setSelection(2, 4);
			expect(editor.selection.from).toBe(2);
			expect(editor.selection.to).toBe(4);
		});
	});

	describe("selectionAtEnd", () => {
		it("returns a text selection positioned at the end of the document", () => {
			editor.setContent("<p>hello</p>");
			expect(editor.selectionAtEnd.head).toBe(6);
		});
	});

	describe("selectionAtPosition", () => {
		it("returns the current selection when position is null", () => {
			editor.setContent("<p>hello</p>");
			editor.setSelection(2, 4);
			const sel = editor.selectionAtPosition(null);
			expect(sel.from).toBe(2);
			expect(sel.to).toBe(4);
		});

		it("returns the start of the document for 'start'", () => {
			editor.setContent("<p>hello</p>");
			const sel = editor.selectionAtPosition("start") as { head: number };
			expect(sel.head).toBe(1);
		});

		it("returns the start of the document for true", () => {
			editor.setContent("<p>hello</p>");
			const sel = editor.selectionAtPosition(true) as { head: number };
			expect(sel.head).toBe(1);
		});

		it("returns the end of the document for 'end'", () => {
			editor.setContent("<p>hello</p>");
			const sel = editor.selectionAtPosition("end") as { head: number };
			expect(sel.head).toBe(6);
		});

		it("returns a collapsed selection at the given position for a number", () => {
			editor.setContent("<p>hello</p>");
			const sel = editor.selectionAtPosition(2);
			expect(sel.from).toBe(2);
			expect(sel.to).toBe(2);
		});
	});

	describe("selectionAtStart", () => {
		it("returns a text selection positioned at the start of the document", () => {
			editor.setContent("<p>hello</p>");
			expect(editor.selectionAtStart.head).toBe(1);
		});
	});

	describe("selectionIsAtEnd", () => {
		it("returns true when the cursor is at the end of the document", () => {
			editor.setContent("<p>hello</p>");
			const end = editor.selectionAtEnd.head;
			editor.setSelection(end, end);
			expect(editor.selectionIsAtEnd).toBe(true);
		});

		it("returns false when the cursor is not at the end", () => {
			editor.setContent("<p>hello</p>");
			editor.setSelection(1, 1);
			expect(editor.selectionIsAtEnd).toBe(false);
		});
	});

	describe("selectionIsAtStart", () => {
		it("returns true when the cursor is at the start of the document", () => {
			editor.setContent("<p>hello</p>");
			editor.setSelection(1, 1);
			expect(editor.selectionIsAtStart).toBe(true);
		});

		it("returns false when the cursor is not at the start", () => {
			editor.setContent("<p>hello</p>");
			editor.setSelection(3, 3);
			expect(editor.selectionIsAtStart).toBe(false);
		});
	});

	describe("setContent", () => {
		it("updates the document content", () => {
			editor.setContent("<p>old content</p>");
			expect(editor.getHTML()).toBe("<p>old content</p>");
			editor.setContent("<p>new content</p>");
			expect(editor.getHTML()).toBe("<p>new content</p>");
		});

		it("does not emit an update event by default", () => {
			const onUpdate = vi.fn();
			editor.on("update", onUpdate);
			editor.setContent("<p>hello</p>");
			expect(onUpdate).not.toHaveBeenCalled();
		});

		it("emits an update event when emitUpdate is true", () => {
			const onUpdate = vi.fn();
			editor.on("update", onUpdate);
			editor.setContent("<p>hello</p>", true);
			expect(onUpdate).toHaveBeenCalledOnce();
		});
	});

	describe("setSelection", () => {
		it("updates the selection to the given positions", () => {
			editor.setContent("<p>hello</p>");
			editor.setSelection(2, 4);
			expect(editor.selection.from).toBe(2);
			expect(editor.selection.to).toBe(4);
		});

		it("clamps out-of-bounds positions to valid document positions", () => {
			editor.setContent("<p>hello</p>");
			editor.setSelection(-5, 9999);
			expect(editor.selection.from).toBeGreaterThanOrEqual(0);
			expect(editor.selection.to).toBeLessThanOrEqual(
				editor.state!.doc.content.size
			);
		});
	});

	describe("state", () => {
		it("returns the current editor state", () => {
			expect(editor.state).toBeDefined();
			expect(editor.state!.doc).toBeDefined();
		});

		it("returns undefined after the editor is destroyed", () => {
			const e = createEditor();
			e.destroy();
			expect(e.state).toBeUndefined();
		});
	});

	describe("toggleMark", () => {
		it("applies a mark to the current selection", () => {
			const e = createEditor({ extensions: [new Bold()] });
			e.setContent("<p>hello</p>");
			e.setSelection(1, 6);
			e.toggleMark("bold");
			expect(e.getHTML()).toBe("<p><strong>hello</strong></p>");
			e.destroy();
		});

		it("removes an active mark from the current selection", () => {
			const e = createEditor({ extensions: [new Bold()] });
			e.setContent("<p><strong>hello</strong></p>");
			e.setSelection(1, 6);
			e.toggleMark("bold");
			expect(e.getHTML()).toBe("<p>hello</p>");
			e.destroy();
		});

		it("does not throw for an unknown mark name", () => {
			expect(() => editor.toggleMark("nonexistent")).not.toThrow();
		});
	});

	describe("updateMark", () => {
		it("updates the attrs of an active mark at the cursor", () => {
			const e = createEditor({ extensions: [new Link()] });
			e.setContent('<p><a href="https://example.com">hello</a></p>');
			e.setSelection(2, 2);
			e.updateMark("link", { href: "https://kirby.tools" });
			expect(e.getMarkAttrs("link")).toMatchObject({
				href: "https://kirby.tools"
			});
			e.destroy();
		});

		it("does not throw for an unknown mark name", () => {
			expect(() => editor.updateMark("nonexistent", {})).not.toThrow();
		});
	});
});
