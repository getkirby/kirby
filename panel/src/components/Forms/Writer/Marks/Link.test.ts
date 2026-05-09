import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	applyPasteRule,
	createSchemaWithMarks,
	getMarkAttrs,
	hasMark,
	mockEditor,
	toHTML
} from "@test/unit/editor";
import utils from "../Utils";
import Link from "./Link";

const mark = new Link();
const schema = createSchemaWithMarks({ link: mark.schema });
const context = { type: schema.marks.link, schema, utils };

describe("Link mark", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { $t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("url");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		describe("link", () => {
			it("emits 'link' event when no modifier key is pressed", () => {
				const mark = new Link();
				const editor = mockEditor({ emit: vi.fn() });
				mark.bindEditor(editor);

				const { link } = mark.commands();
				link(new MouseEvent("click"));

				expect(editor.emit).toHaveBeenCalledWith("link", editor);
			});

			it("removes the mark when alt key is pressed", () => {
				const mark = new Link();
				const editor = mockEditor();
				mark.bindEditor(editor);

				const { link } = mark.commands();
				link(new MouseEvent("click", { altKey: true }));

				expect(editor.removeMark).toHaveBeenCalledWith("link");
			});

			it("removes the mark when meta key is pressed", () => {
				const mark = new Link();
				const editor = mockEditor();
				mark.bindEditor(editor);

				const { link } = mark.commands();
				link(new MouseEvent("click", { metaKey: true }));

				expect(editor.removeMark).toHaveBeenCalledWith("link");
			});
		});

		describe("insertLink", () => {
			it("inserts link as text when selection is empty and mark is not active", () => {
				const mark = new Link();
				const editor = mockEditor({
					insertText: vi.fn(),
					state: { selection: { empty: true } },
					activeMarks: []
				});
				mark.bindEditor(editor);

				const { insertLink } = mark.commands();
				insertLink({ href: "https://example.com" });

				expect(editor.insertText).toHaveBeenCalledWith(
					"https://example.com",
					true
				);
			});

			it("does not insert as text when selection is empty but mark is already active", () => {
				const mark = new Link();
				const editor = mockEditor({
					insertText: vi.fn(),
					state: { selection: { empty: true } },
					activeMarks: ["link"]
				});
				mark.bindEditor(editor);

				const { insertLink } = mark.commands();
				insertLink({ href: "https://example.com" });

				expect(editor.insertText).not.toHaveBeenCalled();
			});

			it("does not insert as text when text is selected", () => {
				const mark = new Link();
				const editor = mockEditor({
					insertText: vi.fn(),
					state: { selection: { empty: false } },
					activeMarks: []
				});
				mark.bindEditor(editor);

				const { insertLink } = mark.commands();
				insertLink({ href: "https://example.com" });

				expect(editor.insertText).not.toHaveBeenCalled();
			});

			it("applies the link mark when href is provided", () => {
				const mark = new Link();
				const editor = mockEditor({
					state: { selection: { empty: false } },
					activeMarks: []
				});
				mark.bindEditor(editor);

				const { insertLink } = mark.commands();
				insertLink({ href: "https://example.com" });

				expect(editor.updateMark).toHaveBeenCalledWith("link", {
					href: "https://example.com"
				});
			});

			it("does not apply the mark when href is absent", () => {
				const mark = new Link();
				const editor = mockEditor({
					state: { selection: { empty: false } },
					activeMarks: []
				});
				mark.bindEditor(editor);

				const { insertLink } = mark.commands();
				insertLink({});

				expect(editor.updateMark).not.toHaveBeenCalled();
			});
		});

		describe("removeLink", () => {
			it("removes the link mark", () => {
				const mark = new Link();
				const editor = mockEditor();
				mark.bindEditor(editor);

				const { removeLink } = mark.commands();
				removeLink();

				expect(editor.removeMark).toHaveBeenCalledWith("link");
			});
		});

		describe("toggleLink", () => {
			it("inserts the link when href is provided", () => {
				const mark = new Link();
				const editor = mockEditor({ command: vi.fn() });
				mark.bindEditor(editor);

				const { toggleLink } = mark.commands();
				toggleLink({ href: "https://example.com" });

				expect(editor.command).toHaveBeenCalledWith("insertLink", {
					href: "https://example.com"
				});
			});

			it("removes the link when href is empty", () => {
				const mark = new Link();
				const editor = mockEditor({ command: vi.fn() });
				mark.bindEditor(editor);

				const { toggleLink } = mark.commands();
				toggleLink({ href: "" });

				expect(editor.command).toHaveBeenCalledWith("removeLink");
			});

			it("removes the link when no attrs are provided", () => {
				const mark = new Link();
				const editor = mockEditor({ command: vi.fn() });
				mark.bindEditor(editor);

				const { toggleLink } = mark.commands();
				toggleLink();

				expect(editor.command).toHaveBeenCalledWith("removeLink");
			});
		});
	});

	describe("name", () => {
		it("returns 'link'", () => {
			expect(mark.name).toBe("link");
		});
	});

	describe("pasteRules", () => {
		const [rule] = mark.pasteRules(context);

		it("converts a URL to link mark when pasting", () => {
			const result = applyPasteRule(schema, rule, "https://example.com");
			expect(result).toBe(
				'<p><a href="https://example.com">https://example.com</a></p>'
			);
		});

		it("converts a URL within text to link mark when pasting", () => {
			const result = applyPasteRule(
				schema,
				rule,
				"Visit https://example.com for more"
			);
			expect(result).toBe(
				'<p>Visit <a href="https://example.com">https://example.com</a> for more</p>'
			);
		});

		it("does not convert plain text to link mark", () => {
			const result = applyPasteRule(schema, rule, "not-a-url");
			expect(result).toBe("<p>not-a-url</p>");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses `<a href='...'>` as link mark", () => {
				const html = '<a href="https://example.com">link</a>';
				expect(hasMark(schema, html, schema.marks.link)).toBe(true);
			});

			it("stores the href attr", () => {
				const html = '<a href="https://example.com">link</a>';
				const attrs = getMarkAttrs(schema, html, schema.marks.link);
				expect(attrs?.href).toBe("https://example.com");
			});

			it("stores the target attr", () => {
				const html = '<a href="https://example.com" target="_blank">link</a>';
				const attrs = getMarkAttrs(schema, html, schema.marks.link);
				expect(attrs?.target).toBe("_blank");
			});

			it("stores the title attr", () => {
				const html = '<a href="https://example.com" title="Example">link</a>';
				const attrs = getMarkAttrs(schema, html, schema.marks.link);
				expect(attrs?.title).toBe("Example");
			});

			it("does not parse mailto links as link mark", () => {
				const html = '<a href="mailto:test@example.com">email</a>';
				expect(hasMark(schema, html, schema.marks.link)).toBe(false);
			});
		});

		describe("toDOM", () => {
			it("renders the link mark as an `<a>` element", () => {
				const linkMark = schema.marks.link.create({
					href: "https://example.com"
				});
				const doc = schema.node("doc", null, [
					schema.node("paragraph", null, [
						schema.text("before"),
						schema.text("link", [linkMark])
					])
				]);
				expect(toHTML(schema, doc)).toBe(
					'<p>before<a href="https://example.com">link</a></p>'
				);
			});

			it("renders with target when provided", () => {
				const linkMark = schema.marks.link.create({
					href: "https://example.com",
					target: "_blank"
				});
				const doc = schema.node("doc", null, [
					schema.node("paragraph", null, [schema.text("link", [linkMark])])
				]);
				expect(toHTML(schema, doc)).toBe(
					'<p><a href="https://example.com" target="_blank">link</a></p>'
				);
			});
		});
	});
});
