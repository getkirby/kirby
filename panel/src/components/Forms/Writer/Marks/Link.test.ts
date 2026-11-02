import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	applyPasteRule,
	click,
	createSchemaWithMarks,
	getMarkAttrs,
	hasMark,
	mockEditor,
	render
} from "@test/unit/editor";
import utils from "../Utils";
import Link from "./Link";

const mark = new Link();
const schema = createSchemaWithMarks({ link: mark.schema });
const context = { type: schema.marks.link, schema, utils };

describe("Link mark", () => {
	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("url");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("registers the commands under the link name", () => {
			expect(Object.keys(mark.commands())).toStrictEqual([
				"link",
				"insertLink",
				"removeLink",
				"toggleLink"
			]);
		});

		it("does not insert a dangerous href", () => {
			const mark = new Link();
			const editor = mockEditor({
				insertText: vi.fn(),
				state: { selection: { empty: true } }
			});
			mark.bindEditor(editor);

			mark.commands().insertLink({ href: "javascript:alert(1)" });

			expect(editor.insertText).not.toHaveBeenCalled();
			expect(editor.updateMark).not.toHaveBeenCalled();
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

	describe("plugins", () => {
		let open: ReturnType<typeof vi.fn>;

		beforeEach(() => {
			open = vi.fn();
			vi.stubGlobal("open", open);
		});

		afterEach(() => {
			vi.unstubAllGlobals();
		});

		it("opens the href in its target when alt-clicking a link", () => {
			const mark = new Link();
			const editor = mockEditor({
				getMarkAttrs: vi.fn(() => ({
					href: "https://example.com",
					target: "_blank"
				}))
			});
			mark.bindEditor(editor);

			click(mark, { altKey: true });

			expect(open).toHaveBeenCalledWith("https://example.com", "_blank");
		});

		it("does not open a dangerous href", () => {
			const mark = new Link();
			const editor = mockEditor({
				getMarkAttrs: vi.fn(() => ({ href: "javascript:alert(1)" }))
			});
			mark.bindEditor(editor);

			click(mark, { altKey: true });

			expect(open).not.toHaveBeenCalled();
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

			it("does not parse a dangerous href as link mark", () => {
				const html = '<a href="javascript:alert(1)">link</a>';
				expect(hasMark(schema, html, schema.marks.link)).toBe(false);
			});
		});

		describe("toDOM", () => {
			it("renders the link mark as an `<a>` element", () => {
				const linkMark = schema.marks.link.create({
					href: "https://example.com"
				});
				expect(render(schema, linkMark, "link")).toBe(
					'<p><a href="https://example.com">link</a></p>'
				);
			});

			it("renders with target when provided", () => {
				const linkMark = schema.marks.link.create({
					href: "https://example.com",
					target: "_blank"
				});
				expect(render(schema, linkMark, "link")).toBe(
					'<p><a href="https://example.com" target="_blank">link</a></p>'
				);
			});
		});
	});
});
