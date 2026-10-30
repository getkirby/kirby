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
import Email from "./Email";

const mark = new Email();
const schema = createSchemaWithMarks({ email: mark.schema });
const context = { type: schema.marks.email, schema, utils };

describe("Email mark", () => {
	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("email");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("registers the commands under the email name", () => {
			expect(Object.keys(mark.commands())).toStrictEqual([
				"email",
				"insertEmail",
				"removeEmail",
				"toggleEmail"
			]);
		});

		it("does not insert an invalid email", () => {
			const mark = new Email();
			const editor = mockEditor({
				insertText: vi.fn(),
				state: { selection: { empty: true } }
			});
			mark.bindEditor(editor);

			mark.commands().insertEmail({ href: "not-an-email" });

			expect(editor.insertText).not.toHaveBeenCalled();
			expect(editor.updateMark).not.toHaveBeenCalled();
		});
	});

	describe("name", () => {
		it("returns 'email'", () => {
			expect(mark.name).toBe("email");
		});
	});

	describe("pasteRules", () => {
		const [rule] = mark.pasteRules(context);

		it("converts an email address to email mark when pasting", () => {
			const result = applyPasteRule(schema, rule, "test@example.com");
			expect(result).toBe(
				'<p><a href="mailto:test@example.com">test@example.com</a></p>'
			);
		});

		it("converts an email address within text to email mark when pasting", () => {
			const result = applyPasteRule(
				schema,
				rule,
				"Write to test@example.com please"
			);
			expect(result).toBe(
				'<p>Write to <a href="mailto:test@example.com">test@example.com</a> please</p>'
			);
		});

		it("does not convert plain text to email mark", () => {
			const result = applyPasteRule(schema, rule, "not-an-email");
			expect(result).toBe("<p>not-an-email</p>");
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

		it("opens a `mailto:` URL when alt-clicking an email link", () => {
			const mark = new Email();
			const editor = mockEditor({
				getMarkAttrs: vi.fn(() => ({ href: "test@example.com" }))
			});
			mark.bindEditor(editor);

			click(mark, { altKey: true });

			expect(open).toHaveBeenCalledWith("mailto:test@example.com");
		});

		it("does not open an invalid email", () => {
			const mark = new Email();
			const editor = mockEditor({
				getMarkAttrs: vi.fn(() => ({ href: "not-an-email" }))
			});
			mark.bindEditor(editor);

			click(mark, { altKey: true });

			expect(open).not.toHaveBeenCalled();
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses `<a href='mailto:...'>` as email mark", () => {
				const html = '<a href="mailto:test@example.com">test@example.com</a>';
				expect(hasMark(schema, html, schema.marks.email)).toBe(true);
			});

			it("strips 'mailto:' prefix from the stored href", () => {
				const html = '<a href="mailto:test@example.com">email</a>';
				const attrs = getMarkAttrs(schema, html, schema.marks.email);
				expect(attrs?.href).toBe("test@example.com");
			});

			it("does not parse links without mailto: prefix as email mark", () => {
				const html = '<a href="https://example.com">link</a>';
				expect(hasMark(schema, html, schema.marks.email)).toBe(false);
			});

			it("does not parse an invalid `mailto:` address as email mark", () => {
				const html = '<a href="mailto:not-an-email">email</a>';
				expect(hasMark(schema, html, schema.marks.email)).toBe(false);
			});
		});

		describe("toDOM", () => {
			it("renders the email mark as an `<a>` element with mailto: prefix", () => {
				const mark = schema.marks.email.create({
					href: "test@example.com"
				});
				expect(render(schema, mark, "an email")).toBe(
					'<p><a href="mailto:test@example.com">an email</a></p>'
				);
			});
		});
	});
});
