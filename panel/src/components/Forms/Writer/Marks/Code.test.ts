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
import Code from "./Code";

const mark = new Code();
const schema = createSchemaWithMarks({ code: mark.schema });
const context = { type: schema.marks.code, schema, utils };

describe("Code mark", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { $t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("code");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("returns a single command toggling the mark", () => {
			const mark = new Code();
			const editor = mockEditor();
			mark.bindEditor(editor);

			const command = mark.commands();
			command();

			expect(editor.toggleMark).toHaveBeenCalledWith("code");
		});
	});

	describe("inputRules", () => {
		const [rule] = mark.inputRules(context);

		it.each([
			["`foo`", "<p><code>foo</code></p>"],
			["`foo bar`", "<p><code>foo bar</code></p>"]
		])("converts %s to code mark", (input, expected) => {
			expect(applyInputRule(schema, rule, input)).toBe(expected);
		});

		it.each([["`foo"], ["foo`"], ["``"]])(
			"does not trigger for %s",
			(input) => {
				expect(applyInputRule(schema, rule, input)).toBeNull();
			}
		);
	});

	describe("keys", () => {
		it("maps Mod-` to toggle the mark", () => {
			const mark = new Code();
			const editor = mockEditor();
			mark.bindEditor(editor);

			mark.keys()["Mod-`"]();

			expect(editor.toggleMark).toHaveBeenCalledWith("code");
		});
	});

	describe("name", () => {
		it("returns 'code'", () => {
			expect(mark.name).toBe("code");
		});
	});

	describe("pasteRules", () => {
		const [rule] = mark.pasteRules(context);

		it.each([
			["`foo`", "<p><code>foo</code></p>"],
			["`foo bar`", "<p><code>foo bar</code></p>"],
			["`foo` and `bar`", "<p><code>foo</code> and <code>bar</code></p>"],
			["before `foo` after", "<p>before <code>foo</code> after</p>"]
		])("converts %s to code mark when pasting", (input, expected) => {
			expect(applyPasteRule(schema, rule, input)).toBe(expected);
		});

		it.each([
			["`foo", "<p>`foo</p>"],
			["foo`", "<p>foo`</p>"],
			["``", "<p>``</p>"]
		])("does not apply code for %s", (input, expected) => {
			expect(applyPasteRule(schema, rule, input)).toBe(expected);
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses `<code>` as code mark", () => {
				const html = "<code>foo</code>";
				expect(hasMark(schema, html, schema.marks.code)).toBe(true);
			});
		});

		describe("toDOM", () => {
			it("renders the code mark as a `<code>` element", () => {
				const mark = schema.marks.code.create();
				const doc = schema.node("doc", null, [
					schema.node("paragraph", null, [
						schema.text("foo"),
						schema.text("bar", [mark])
					])
				]);
				expect(toHTML(schema, doc)).toBe("<p>foo<code>bar</code></p>");
			});
		});
	});
});
