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
import Italic from "./Italic";

const mark = new Italic();
const schema = createSchemaWithMarks({ italic: mark.schema });
const context = { type: schema.marks.italic, schema, utils };

describe("Italic mark", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("italic");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("returns a single command toggling the mark", () => {
			const mark = new Italic();
			const editor = mockEditor();
			mark.bindEditor(editor);

			const command = mark.commands();
			command();

			expect(editor.toggleMark).toHaveBeenCalledWith("italic");
		});
	});

	describe("inputRules", () => {
		describe("* delimiter", () => {
			const [rule] = mark.inputRules(context);

			it.each([
				["*foo*", "<p><em>foo</em></p>"],
				["*x*", "<p><em>x</em></p>"],
				["*foo bar*", "<p><em>foo bar</em></p>"],
				["*Mitarbeiter*innen*", "<p><em>Mitarbeiter*innen</em></p>"],
				["*Lehrer*in*", "<p><em>Lehrer*in</em></p>"],
				["*Bürger*innen*", "<p><em>Bürger*innen</em></p>"]
			])("converts %s to italic mark", (input, expected) => {
				expect(applyInputRule(schema, rule, input)).toBe(expected);
			});

			it.each([
				["word*foo*"],
				["*foo"],
				["* foo*"],
				["*foo *"],
				["* foo *"],
				["foo*bar fox*baz"],
				["**"],
				["* *"]
			])("does not trigger for %s", (input) => {
				expect(applyInputRule(schema, rule, input)).toBeNull();
			});
		});

		describe("_ delimiter", () => {
			const [, rule] = mark.inputRules(context);

			it.each([
				["_foo_", "<p><em>foo</em></p>"],
				["_x_", "<p><em>x</em></p>"],
				["_foo bar_", "<p><em>foo bar</em></p>"]
			])("converts %s to italic mark", (input, expected) => {
				expect(applyInputRule(schema, rule, input)).toBe(expected);
			});

			it.each([
				["word_foo_"],
				["_foo"],
				["_ foo_"],
				["_foo _"],
				["_ foo _"],
				["foo_bar fox_baz"],
				["__"],
				["_ _"]
			])("does not trigger for %s", (input) => {
				expect(applyInputRule(schema, rule, input)).toBeNull();
			});
		});
	});

	describe("keys", () => {
		it("maps Mod-i to toggle the mark", () => {
			const mark = new Italic();
			const editor = mockEditor();
			mark.bindEditor(editor);

			mark.keys()["Mod-i"]();

			expect(editor.toggleMark).toHaveBeenCalledWith("italic");
		});
	});

	describe("name", () => {
		it("returns 'italic'", () => {
			expect(mark.name).toBe("italic");
		});
	});

	describe("pasteRules", () => {
		describe("* delimiter", () => {
			const [rule] = mark.pasteRules(context);

			it.each([
				["*foo*", "<p><em>foo</em></p>"],
				["*x*", "<p><em>x</em></p>"],
				["*foo bar*", "<p><em>foo bar</em></p>"],
				["*foo* and *bar*", "<p><em>foo</em> and <em>bar</em></p>"],
				["before *foo* after", "<p>before <em>foo</em> after</p>"],
				["*Mitarbeiter*innen*", "<p><em>Mitarbeiter*innen</em></p>"],
				["*Lehrer*in*", "<p><em>Lehrer*in</em></p>"],
				["*Bürger*innen*", "<p><em>Bürger*innen</em></p>"],
				[
					"Liebe *Mitarbeiter*innen*",
					"<p>Liebe <em>Mitarbeiter*innen</em></p>"
				]
			])("converts %s to italic mark when pasting", (input, expected) => {
				expect(applyPasteRule(schema, rule, input)).toBe(expected);
			});

			it.each([
				["word*foo*", "<p>word*foo*</p>"],
				["*foo", "<p>*foo</p>"],
				["* foo*", "<p>* foo*</p>"],
				["*foo *", "<p>*foo *</p>"],
				["* foo *", "<p>* foo *</p>"],
				["foo*bar fox*baz", "<p>foo*bar fox*baz</p>"],
				["**", "<p>**</p>"],
				["* *", "<p>* *</p>"]
			])("does not apply italic for %s", (input, expected) => {
				expect(applyPasteRule(schema, rule, input)).toBe(expected);
			});
		});

		describe("_ delimiter", () => {
			const [, rule] = mark.pasteRules(context);

			it.each([
				["_foo_", "<p><em>foo</em></p>"],
				["_x_", "<p><em>x</em></p>"],
				["_foo bar_", "<p><em>foo bar</em></p>"],
				["_foo_ and _bar_", "<p><em>foo</em> and <em>bar</em></p>"],
				["before _foo_ after", "<p>before <em>foo</em> after</p>"]
			])("converts %s to italic mark when pasting", (input, expected) => {
				expect(applyPasteRule(schema, rule, input)).toBe(expected);
			});

			it.each([
				["word_foo_", "<p>word_foo_</p>"],
				["_foo", "<p>_foo</p>"],
				["_ foo_", "<p>_ foo_</p>"],
				["_foo _", "<p>_foo _</p>"],
				["_ foo _", "<p>_ foo _</p>"],
				["foo_bar fox_baz", "<p>foo_bar fox_baz</p>"],
				["__", "<p>__</p>"],
				["_ _", "<p>_ _</p>"]
			])("does not apply italic for %s", (input, expected) => {
				expect(applyPasteRule(schema, rule, input)).toBe(expected);
			});
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses `<i>` as italic mark", () => {
				const html = "<i>foo</i>";
				expect(hasMark(schema, html, schema.marks.italic)).toBe(true);
			});

			it("parses `<em>` as italic mark", () => {
				const html = "<em>foo</em>";
				expect(hasMark(schema, html, schema.marks.italic)).toBe(true);
			});

			it("parses `font-style: italic` style as italic mark", () => {
				const html = '<span style="font-style: italic">foo</span>';
				expect(hasMark(schema, html, schema.marks.italic)).toBe(true);
			});

			it("does not parse other `font-style` style as italic mark", () => {
				const html = '<span style="font-style: oblique">foo</span>';
				expect(hasMark(schema, html, schema.marks.italic)).toBe(false);
			});
		});

		describe("toDOM", () => {
			it("renders the italic mark as an `<em>` element", () => {
				const mark = schema.marks.italic.create();
				const doc = schema.node("doc", null, [
					schema.node("paragraph", null, [
						schema.text("foo"),
						schema.text("bar", [mark])
					])
				]);
				expect(toHTML(schema, doc)).toBe("<p>foo<em>bar</em></p>");
			});
		});
	});
});
