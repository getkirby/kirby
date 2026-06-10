import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	createSchemaWithMarks,
	hasMark,
	mockEditor,
	toHTML
} from "@test/unit/editor";
import Sub from "./Sub";

const mark = new Sub();
const schema = createSchemaWithMarks({ sub: mark.schema });

describe("Sub mark", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("subscript");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("returns a single command toggling the mark", () => {
			const mark = new Sub();
			const editor = mockEditor();
			mark.bindEditor(editor);

			const command = mark.commands();
			command();

			expect(editor.toggleMark).toHaveBeenCalledWith("sub");
		});
	});

	describe("name", () => {
		it("returns 'sub'", () => {
			expect(mark.name).toBe("sub");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses `<sub>` as sub mark", () => {
				const html = "<sub>foo</sub>";
				expect(hasMark(schema, html, schema.marks.sub)).toBe(true);
			});
		});

		describe("toDOM", () => {
			it("renders the sub mark as a `<sub>` element", () => {
				const mark = schema.marks.sub.create();
				const doc = schema.node("doc", null, [
					schema.node("paragraph", null, [
						schema.text("foo"),
						schema.text("bar", [mark])
					])
				]);
				expect(toHTML(schema, doc)).toBe("<p>foo<sub>bar</sub></p>");
			});
		});
	});
});
