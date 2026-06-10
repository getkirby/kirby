import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	applyInputRule,
	applyPasteRule,
	createSchemaWithMarks,
	hasMark,
	mockEditor,
	toHTML
} from "@test/unit/editor";
import utils from "../Utils";
import Bold from "./Bold";

const mark = new Bold();
const schema = createSchemaWithMarks({ bold: mark.schema });
const context = { type: schema.marks.bold, schema, utils };

describe("Bold mark", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("bold");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("returns a single command toggling the mark", () => {
			const mark = new Bold();
			const editor = mockEditor();
			mark.bindEditor(editor);

			const command = mark.commands();
			command();

			expect(editor.toggleMark).toHaveBeenCalledWith("bold");
		});
	});

	describe("inputRules", () => {
		describe("** delimiter", () => {
			const [rule] = mark.inputRules(context);

			it.each([
				["**foo**", "<p><strong>foo</strong></p>"],
				["**x**", "<p><strong>x</strong></p>"],
				["**foo bar**", "<p><strong>foo bar</strong></p>"],
				["**Mitarbeiter*innen**", "<p><strong>Mitarbeiter*innen</strong></p>"],
				["**Lehrer*in**", "<p><strong>Lehrer*in</strong></p>"],
				["**Bürger*innen**", "<p><strong>Bürger*innen</strong></p>"]
			])("converts %s to bold mark", (input, expected) => {
				expect(applyInputRule(schema, rule, input)).toBe(expected);
			});

			it.each([
				["word**foo**"],
				["**foo"],
				["** foo**"],
				["**foo **"],
				["** foo **"],
				["****"],
				["** **"]
			])("does not trigger for %s", (input) => {
				expect(applyInputRule(schema, rule, input)).toBeNull();
			});
		});

		describe("__ delimiter", () => {
			const [, rule] = mark.inputRules(context);

			it.each([
				["__foo__", "<p><strong>foo</strong></p>"],
				["__x__", "<p><strong>x</strong></p>"],
				["__foo bar__", "<p><strong>foo bar</strong></p>"]
			])("converts %s to bold mark", (input, expected) => {
				expect(applyInputRule(schema, rule, input)).toBe(expected);
			});

			it.each([
				["word__foo__"],
				["__foo"],
				["__ foo__"],
				["__foo __"],
				["__ foo __"],
				["____"],
				["__ __"]
			])("does not trigger for %s", (input) => {
				expect(applyInputRule(schema, rule, input)).toBeNull();
			});
		});
	});

	describe("keys", () => {
		it("maps Mod-b to toggle the mark", () => {
			const mark = new Bold();
			const editor = mockEditor();
			mark.bindEditor(editor);

			mark.keys()["Mod-b"]();

			expect(editor.toggleMark).toHaveBeenCalledWith("bold");
		});
	});

	describe("name", () => {
		it("returns 'bold'", () => {
			expect(mark.name).toBe("bold");
		});
	});

	describe("pasteRules", () => {
		describe("** delimiter", () => {
			const [rule] = mark.pasteRules(context);

			it.each([
				["**foo**", "<p><strong>foo</strong></p>"],
				["**x**", "<p><strong>x</strong></p>"],
				["**foo bar**", "<p><strong>foo bar</strong></p>"],
				[
					"**foo** and **bar**",
					"<p><strong>foo</strong> and <strong>bar</strong></p>"
				],
				["before **foo** after", "<p>before <strong>foo</strong> after</p>"],
				["**Mitarbeiter*innen**", "<p><strong>Mitarbeiter*innen</strong></p>"],
				["**Lehrer*in**", "<p><strong>Lehrer*in</strong></p>"],
				["**Bürger*innen**", "<p><strong>Bürger*innen</strong></p>"],
				[
					"Liebe **Mitarbeiter*innen**",
					"<p>Liebe <strong>Mitarbeiter*innen</strong></p>"
				]
			])("converts %s to bold mark when pasting", (input, expected) => {
				expect(applyPasteRule(schema, rule, input)).toBe(expected);
			});

			it.each([
				["word**foo**", "<p>word**foo**</p>"],
				["**foo", "<p>**foo</p>"],
				["** foo**", "<p>** foo**</p>"],
				["**foo **", "<p>**foo **</p>"],
				["** foo **", "<p>** foo **</p>"],
				["****", "<p>****</p>"],
				["** **", "<p>** **</p>"]
			])("does not apply bold for %s", (input, expected) => {
				expect(applyPasteRule(schema, rule, input)).toBe(expected);
			});
		});

		describe("__ delimiter", () => {
			const [, rule] = mark.pasteRules(context);

			it.each([
				["__foo__", "<p><strong>foo</strong></p>"],
				["__x__", "<p><strong>x</strong></p>"],
				["__foo bar__", "<p><strong>foo bar</strong></p>"],
				[
					"__foo__ and __bar__",
					"<p><strong>foo</strong> and <strong>bar</strong></p>"
				],
				["before __foo__ after", "<p>before <strong>foo</strong> after</p>"]
			])("converts %s to bold mark when pasting", (input, expected) => {
				expect(applyPasteRule(schema, rule, input)).toBe(expected);
			});

			it.each([
				["word__foo__", "<p>word__foo__</p>"],
				["__foo", "<p>__foo</p>"],
				["__ foo__", "<p>__ foo__</p>"],
				["__foo __", "<p>__foo __</p>"],
				["__ foo __", "<p>__ foo __</p>"],
				["____", "<p>____</p>"],
				["__ __", "<p>__ __</p>"]
			])("does not apply bold for %s", (input, expected) => {
				expect(applyPasteRule(schema, rule, input)).toBe(expected);
			});
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses `<strong>` as bold mark", () => {
				const html = "<strong>foo</strong>";
				expect(hasMark(schema, html, schema.marks.bold)).toBe(true);
			});

			it("parses `<b>` as bold mark", () => {
				const html = "<b>foo</b>";
				expect(hasMark(schema, html, schema.marks.bold)).toBe(true);
			});

			it('does not parse `<b style="font-weight: normal">` as bold mark', () => {
				const html = '<b style="font-weight: normal">foo</b>';
				expect(hasMark(schema, html, schema.marks.bold)).toBe(false);
			});

			it("parses `font-weight: bold` style as bold mark", () => {
				const html = '<span style="font-weight: bold">foo</span>';
				expect(hasMark(schema, html, schema.marks.bold)).toBe(true);
			});

			it("parses `font-weight: 700` style as bold mark", () => {
				const html = '<span style="font-weight: 700">foo</span>';
				expect(hasMark(schema, html, schema.marks.bold)).toBe(true);
			});

			it("does not parse `font-weight: 400` style as bold mark", () => {
				const html = '<span style="font-weight: 400">foo</span>';
				expect(hasMark(schema, html, schema.marks.bold)).toBe(false);
			});
		});

		describe("toDOM", () => {
			it("renders the bold mark as a `<strong>` element", () => {
				const mark = schema.marks.bold.create();
				const doc = schema.node("doc", null, [
					schema.node("paragraph", null, [
						schema.text("foo"),
						schema.text("bar", [mark])
					])
				]);
				expect(toHTML(schema, doc)).toBe("<p>foo<strong>bar</strong></p>");
			});
		});
	});
});
