import { describe, expect, it } from "vitest";
import {
	createSchemaWithMarks,
	hasMark,
	mockEditor,
	render
} from "@test/unit/editor";
import Sup from "./Sup";

const mark = new Sup();
const schema = createSchemaWithMarks({ sup: mark.schema });

describe("Sup mark", () => {
	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("superscript");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("returns a single command toggling the mark", () => {
			const mark = new Sup();
			const editor = mockEditor();
			mark.bindEditor(editor);

			const command = mark.commands();
			command();

			expect(editor.toggleMark).toHaveBeenCalledWith("sup");
		});
	});

	describe("name", () => {
		it("returns 'sup'", () => {
			expect(mark.name).toBe("sup");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses `<sup>` as sup mark", () => {
				const html = "<sup>foo</sup>";
				expect(hasMark(schema, html, schema.marks.sup)).toBe(true);
			});
		});

		describe("toDOM", () => {
			it("renders the sup mark as a `<sup>` element", () => {
				const mark = schema.marks.sup.create();
				expect(render(schema, mark, "bar")).toBe("<p><sup>bar</sup></p>");
			});
		});
	});
});
