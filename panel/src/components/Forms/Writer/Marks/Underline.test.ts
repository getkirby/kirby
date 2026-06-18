import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	createSchemaWithMarks,
	hasMark,
	mockEditor,
	toHTML
} from "@test/unit/editor";
import Underline from "./Underline";

const mark = new Underline();
const schema = createSchemaWithMarks({ underline: mark.schema });

describe("Underline mark", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the buttons config", () => {
			const button = mark.button;
			expect(button.icon).toBe("underline");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("returns a single command toggling the mark", () => {
			const mark = new Underline();
			const editor = mockEditor();
			mark.bindEditor(editor);

			const command = mark.commands();
			command();

			expect(editor.toggleMark).toHaveBeenCalledWith("underline");
		});
	});

	describe("keys", () => {
		it("maps Mod-u to toggle the mark", () => {
			const mark = new Underline();
			const editor = mockEditor();
			mark.bindEditor(editor);

			mark.keys()["Mod-u"]();

			expect(editor.toggleMark).toHaveBeenCalledWith("underline");
		});
	});

	describe("name", () => {
		it("returns 'underline'", () => {
			expect(mark.name).toBe("underline");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses `<u>` as underline mark", () => {
				const html = "<u>foo</u>";
				expect(hasMark(schema, html, schema.marks.underline)).toBe(true);
			});

			it("parses `text-decoration: underline` style as underline mark", () => {
				const html = '<span style="text-decoration: underline">foo</span>';
				expect(hasMark(schema, html, schema.marks.underline)).toBe(true);
			});

			it("does not parse other `text-decoration` values as underline mark", () => {
				const html = '<span style="text-decoration: line-through">foo</span>';
				expect(hasMark(schema, html, schema.marks.underline)).toBe(false);
			});
		});

		describe("toDOM", () => {
			it("renders the underline mark as `<u>` element", () => {
				const mark = schema.marks.underline.create();
				const doc = schema.node("doc", null, [
					schema.node("paragraph", null, [schema.text("hello", [mark])])
				]);
				expect(toHTML(schema, doc)).toBe("<p><u>hello</u></p>");
			});
		});
	});
});
