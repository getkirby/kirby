import { describe, expect, it, onTestFinished, vi } from "vitest";
import { mockEditor } from "@test/unit/editor";
import Editor from "../Editor";
import Bold from "./Bold";
import Clear from "./Clear";
import Italic from "./Italic";

const mark = new Clear();

function createEditor(content: string): { editor: Editor; mark: Clear } {
	const element = document.createElement("div");
	document.body.appendChild(element);

	const mark = new Clear();
	const editor = new Editor({
		content,
		element,
		extensions: [new Bold(), new Italic(), mark]
	});
	onTestFinished(() => editor.destroy());

	return { editor, mark };
}

describe("Clear mark", () => {
	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("clear");
			expect(button.label).toBeDefined();
		});
	});

	describe("clear", () => {
		it("removes all active marks with a single dispatch", () => {
			const { editor, mark } = createEditor(
				"<p><strong><em>hello</em></strong></p>"
			);
			editor.setSelection(1, 6);

			const dispatch = vi.spyOn(editor.view!, "dispatch");
			mark.clear();

			expect(dispatch).toHaveBeenCalledOnce();
			expect(editor.getHTML()).toBe("<p>hello</p>");
		});

		it("removes marks that only cover parts of the selection", () => {
			const { editor, mark } = createEditor(
				"<p><strong>hel</strong><em>lo</em></p>"
			);
			editor.setSelection(1, 6);
			mark.clear();

			expect(editor.getHTML()).toBe("<p>hello</p>");
		});

		it("keeps marks outside of the selection", () => {
			const { editor, mark } = createEditor(
				"<p><strong><em>hello</em></strong></p>"
			);
			editor.setSelection(1, 3);
			mark.clear();

			expect(editor.getHTML()).toBe("<p>he<strong><em>llo</em></strong></p>");
		});

		it("does nothing when state is not available", () => {
			const mark = new Clear();
			const dispatch = vi.fn();

			const editor = mockEditor({
				activeMarks: ["bold"],
				state: undefined,
				view: { dispatch }
			});

			mark.bindEditor(editor);
			mark.clear();

			expect(dispatch).not.toHaveBeenCalled();
		});

		it("does nothing when there are no active marks", () => {
			const { editor, mark } = createEditor("<p>hello</p>");
			editor.setSelection(1, 6);

			const dispatch = vi.spyOn(editor.view!, "dispatch");
			mark.clear();

			expect(dispatch).not.toHaveBeenCalled();
		});
	});

	describe("commands", () => {
		it("calls clear() when the command is invoked", () => {
			const mark = new Clear();
			const editor = mockEditor();
			mark.bindEditor(editor);

			vi.spyOn(mark, "clear").mockImplementation(() => {});

			const command = mark.commands();
			command();

			expect(mark.clear).toHaveBeenCalledOnce();
		});
	});

	describe("name", () => {
		it("returns 'clear'", () => {
			expect(mark.name).toBe("clear");
		});
	});

	describe("schema", () => {
		it("neither parses nor renders anything", () => {
			expect(mark.schema).toEqual({});
		});
	});
});
