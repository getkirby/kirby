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
import Email from "./Email";

const mark = new Email();
const schema = createSchemaWithMarks({ email: mark.schema });
const context = { type: schema.marks.email, schema, utils };

describe("Email mark", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("email");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		describe("email", () => {
			it("emits 'email' event when no modifier key is pressed", () => {
				const mark = new Email();
				const editor = mockEditor({ emit: vi.fn() });
				mark.bindEditor(editor);

				const { email } = mark.commands();
				email(new MouseEvent("click"));

				expect(editor.emit).toHaveBeenCalledWith("email", editor);
			});

			it("removes the mark when alt key is pressed", () => {
				const mark = new Email();
				const editor = mockEditor();
				mark.bindEditor(editor);

				const { email } = mark.commands();
				email(new MouseEvent("click", { altKey: true }));

				expect(editor.removeMark).toHaveBeenCalledWith("email");
			});

			it("removes the mark when meta key is pressed", () => {
				const mark = new Email();
				const editor = mockEditor();
				mark.bindEditor(editor);

				const { email } = mark.commands();
				email(new MouseEvent("click", { metaKey: true }));

				expect(editor.removeMark).toHaveBeenCalledWith("email");
			});
		});

		describe("insertEmail", () => {
			it("inserts email as text when selection is empty", () => {
				const mark = new Email();
				const editor = mockEditor({
					insertText: vi.fn(),
					state: { selection: { empty: true } }
				});
				mark.bindEditor(editor);

				const { insertEmail } = mark.commands();
				insertEmail({ href: "test@example.com" });

				expect(editor.insertText).toHaveBeenCalledWith(
					"test@example.com",
					true
				);
			});

			it("applies the email mark when href is provided", () => {
				const mark = new Email();
				const editor = mockEditor({
					state: { selection: { empty: false } }
				});
				mark.bindEditor(editor);

				const { insertEmail } = mark.commands();
				insertEmail({ href: "test@example.com" });

				expect(editor.updateMark).toHaveBeenCalledWith("email", {
					href: "test@example.com"
				});
			});

			it("does not apply the mark when href is absent", () => {
				const mark = new Email();
				const editor = mockEditor({
					state: { selection: { empty: false } }
				});
				mark.bindEditor(editor);

				const { insertEmail } = mark.commands();
				insertEmail({});

				expect(editor.updateMark).not.toHaveBeenCalled();
			});

			it("does not insert as text when href is absent", () => {
				const mark = new Email();
				const editor = mockEditor({
					insertText: vi.fn(),
					state: { selection: { empty: true } }
				});
				mark.bindEditor(editor);

				const { insertEmail } = mark.commands();
				insertEmail({});

				expect(editor.insertText).not.toHaveBeenCalled();
			});
		});

		describe("removeEmail", () => {
			it("removes the email mark", () => {
				const mark = new Email();
				const editor = mockEditor();
				mark.bindEditor(editor);

				const { removeEmail } = mark.commands();
				removeEmail();

				expect(editor.removeMark).toHaveBeenCalledWith("email");
			});
		});

		describe("toggleEmail", () => {
			it("inserts the email when href is provided", () => {
				const mark = new Email();
				const editor = mockEditor({ command: vi.fn() });
				mark.bindEditor(editor);

				const { toggleEmail } = mark.commands();
				toggleEmail({ href: "test@example.com" });

				expect(editor.command).toHaveBeenCalledWith("insertEmail", {
					href: "test@example.com"
				});
			});

			it("removes the email when href is empty", () => {
				const mark = new Email();
				const editor = mockEditor({ command: vi.fn() });
				mark.bindEditor(editor);

				const { toggleEmail } = mark.commands();
				toggleEmail({ href: "" });

				expect(editor.command).toHaveBeenCalledWith("removeEmail");
			});

			it("removes the email when no attrs are provided", () => {
				const mark = new Email();
				const editor = mockEditor({ command: vi.fn() });
				mark.bindEditor(editor);

				const { toggleEmail } = mark.commands();
				toggleEmail();

				expect(editor.command).toHaveBeenCalledWith("removeEmail");
			});
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
		const handleClick = (mark: Email) =>
			mark.plugins()[0].props?.handleClick as unknown as (
				view: unknown,
				pos: number,
				event: MouseEvent
			) => void;

		it("opens a `mailto:` URL when alt-clicking an email link", () => {
			const mark = new Email();
			const editor = mockEditor({
				getMarkAttrs: vi.fn(() => ({ href: "test@example.com" }))
			});
			mark.bindEditor(editor);

			const open = vi.fn();
			vi.stubGlobal("open", open);

			handleClick(mark)(null, 0, {
				altKey: true,
				target: document.createElement("a"),
				stopPropagation: vi.fn()
			} as unknown as MouseEvent);

			expect(open).toHaveBeenCalledWith("mailto:test@example.com");
		});

		it("does not open a URL when the alt key is not pressed", () => {
			const mark = new Email();
			const editor = mockEditor({
				getMarkAttrs: vi.fn(() => ({ href: "test@example.com" }))
			});
			mark.bindEditor(editor);

			const open = vi.fn();
			vi.stubGlobal("open", open);

			handleClick(mark)(null, 0, {
				altKey: false,
				target: document.createElement("a"),
				stopPropagation: vi.fn()
			} as unknown as MouseEvent);

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
		});

		describe("toDOM", () => {
			it("renders the email mark as an `<a>` element with mailto: prefix", () => {
				const mark = schema.marks.email.create({
					href: "test@example.com"
				});
				const doc = schema.node("doc", null, [
					schema.node("paragraph", null, [
						schema.text("Write me "),
						schema.text("an email", [mark])
					])
				]);
				expect(toHTML(schema, doc)).toBe(
					'<p>Write me <a href="mailto:test@example.com">an email</a></p>'
				);
			});
		});
	});
});
