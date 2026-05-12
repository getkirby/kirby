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
import Strike from "./Strike";

const mark = new Strike();
const schema = createSchemaWithMarks({ strike: mark.schema });
const context = { type: schema.marks.strike, schema, utils };

describe("Strike mark", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("strikethrough");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("returns a single command toggling the mark", () => {
			const mark = new Strike();
			const editor = mockEditor();
			mark.bindEditor(editor);

			const command = mark.commands();
			command();

			expect(editor.toggleMark).toHaveBeenCalledWith("strike");
		});
	});

	describe("inputRules", () => {
		const [rule] = mark.inputRules(context);

		it.each([
			["~foo~", "<p><s>foo</s></p>"],
			["~foo bar~", "<p><s>foo bar</s></p>"]
		])("converts %s to strike mark", (input, expected) => {
			expect(applyInputRule(schema, rule, input)).toBe(expected);
		});

		it.each([["~foo"], ["~~"]])("does not trigger for %s", (input) => {
			expect(applyInputRule(schema, rule, input)).toBeNull();
		});
	});

	describe("keys", () => {
		it("maps Mod-d to toggle the mark", () => {
			const mark = new Strike();
			const editor = mockEditor();
			mark.bindEditor(editor);

			mark.keys()["Mod-d"]();

			expect(editor.toggleMark).toHaveBeenCalledWith("strike");
		});
	});

	describe("name", () => {
		it("returns 'strike'", () => {
			expect(mark.name).toBe("strike");
		});
	});

	describe("pasteRules", () => {
		const [rule] = mark.pasteRules(context);

		it.each([
			["~foo~", "<p><s>foo</s></p>"],
			["~foo bar~", "<p><s>foo bar</s></p>"],
			["~foo~ and ~bar~", "<p><s>foo</s> and <s>bar</s></p>"],
			["before ~foo~ after", "<p>before <s>foo</s> after</p>"]
		])("converts %s to strike mark when pasting", (input, expected) => {
			expect(applyPasteRule(schema, rule, input)).toBe(expected);
		});

		it.each([
			["~foo", "<p>~foo</p>"],
			["~~", "<p>~~</p>"]
		])("does not apply strike for %s", (input, expected) => {
			expect(applyPasteRule(schema, rule, input)).toBe(expected);
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses `<s>` as strike mark", () => {
				const html = "<s>foo</s>";
				expect(hasMark(schema, html, schema.marks.strike)).toBe(true);
			});

			it("parses `<del>` as strike mark", () => {
				const html = "<del>foo</del>";
				expect(hasMark(schema, html, schema.marks.strike)).toBe(true);
			});

			it("parses `<strike>` as strike mark", () => {
				const html = "<strike>foo</strike>";
				expect(hasMark(schema, html, schema.marks.strike)).toBe(true);
			});

			it("parses `text-decoration: line-through` style as strike mark", () => {
				const html = '<span style="text-decoration: line-through">foo</span>';
				expect(hasMark(schema, html, schema.marks.strike)).toBe(true);
			});

			it("does not parse other `text-decoration` values as strike mark", () => {
				const html = '<span style="text-decoration: underline">foo</span>';
				expect(hasMark(schema, html, schema.marks.strike)).toBe(false);
			});
		});

		describe("toDOM", () => {
			it("renders the strike mark as a `<s>` element", () => {
				const mark = schema.marks.strike.create();
				const doc = schema.node("doc", null, [
					schema.node("paragraph", null, [
						schema.text("foo"),
						schema.text("bar", [mark])
					])
				]);
				expect(toHTML(schema, doc)).toBe("<p>foo<s>bar</s></p>");
			});
		});
	});
});
