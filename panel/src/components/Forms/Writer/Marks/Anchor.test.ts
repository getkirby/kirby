import { describe, expect, it, vi } from "vitest";
import type { MarkSpec } from "prosemirror-model";
import { click, mockEditor } from "@test/unit/editor";
import Anchor, { type AnchorAttrs } from "./Anchor";

/**
 * Accepts every href but `rejected`
 */
class Fixture extends Anchor {
	opened = vi.fn();

	protected accepts(href: string | null): boolean {
		return href !== "rejected";
	}

	get name() {
		return "anchor";
	}

	protected open(attrs: AnchorAttrs): void {
		this.opened(attrs);
	}

	get schema(): MarkSpec {
		return {};
	}
}

function setup(overrides = {}) {
	const mark = new Fixture();
	const editor = mockEditor(overrides);
	mark.bindEditor(editor);
	return { editor, mark };
}

describe("Anchor mark", () => {
	describe("commands", () => {
		it("registers the commands under the mark name", () => {
			const { mark } = setup();

			expect(Object.keys(mark.commands())).toStrictEqual([
				"anchor",
				"insertAnchor",
				"removeAnchor",
				"toggleAnchor"
			]);
		});

		describe("anchor", () => {
			it("emits the mark name when no modifier key is pressed", () => {
				const { editor, mark } = setup({ emit: vi.fn() });

				mark.commands().anchor(new MouseEvent("click"));

				expect(editor.emit).toHaveBeenCalledWith("anchor", editor);
			});

			it("removes the mark when alt key is pressed", () => {
				const { editor, mark } = setup();

				mark.commands().anchor(new MouseEvent("click", { altKey: true }));

				expect(editor.removeMark).toHaveBeenCalledWith("anchor");
			});

			it("removes the mark when meta key is pressed", () => {
				const { editor, mark } = setup();

				mark.commands().anchor(new MouseEvent("click", { metaKey: true }));

				expect(editor.removeMark).toHaveBeenCalledWith("anchor");
			});
		});

		describe("insertAnchor", () => {
			it("inserts the href as text when selection is empty and mark is not active", () => {
				const { editor, mark } = setup({
					insertText: vi.fn(),
					state: { selection: { empty: true } }
				});

				mark.commands().insertAnchor({ href: "https://example.com" });

				expect(editor.insertText).toHaveBeenCalledWith(
					"https://example.com",
					true
				);
			});

			it("does not insert as text when selection is empty but mark is already active", () => {
				const { editor, mark } = setup({
					insertText: vi.fn(),
					state: { selection: { empty: true } },
					activeMarks: ["anchor"]
				});

				mark.commands().insertAnchor({ href: "https://example.com" });

				expect(editor.insertText).not.toHaveBeenCalled();
			});

			it("does not insert as text when text is selected", () => {
				const { editor, mark } = setup({
					insertText: vi.fn(),
					state: { selection: { empty: false } }
				});

				mark.commands().insertAnchor({ href: "https://example.com" });

				expect(editor.insertText).not.toHaveBeenCalled();
			});

			it("applies the mark when href is provided", () => {
				const { editor, mark } = setup({
					state: { selection: { empty: false } }
				});

				mark.commands().insertAnchor({ href: "https://example.com" });

				expect(editor.updateMark).toHaveBeenCalledWith("anchor", {
					href: "https://example.com"
				});
			});

			it("does nothing when href is absent", () => {
				const { editor, mark } = setup({
					insertText: vi.fn(),
					state: { selection: { empty: true } }
				});

				mark.commands().insertAnchor({});

				expect(editor.insertText).not.toHaveBeenCalled();
				expect(editor.updateMark).not.toHaveBeenCalled();
			});

			it("does not insert a rejected href", () => {
				const { editor, mark } = setup({
					insertText: vi.fn(),
					state: { selection: { empty: true } }
				});

				mark.commands().insertAnchor({ href: "rejected" });

				expect(editor.insertText).not.toHaveBeenCalled();
				expect(editor.updateMark).not.toHaveBeenCalled();
				expect(editor.removeMark).not.toHaveBeenCalled();
			});

			it("removes an active mark for a rejected href", () => {
				const { editor, mark } = setup({
					state: { selection: { empty: false } },
					activeMarks: ["anchor"]
				});

				mark.commands().insertAnchor({ href: "rejected" });

				expect(editor.removeMark).toHaveBeenCalledWith("anchor");
				expect(editor.updateMark).not.toHaveBeenCalled();
			});
		});

		describe("removeAnchor", () => {
			it("removes the mark", () => {
				const { editor, mark } = setup();

				mark.commands().removeAnchor();

				expect(editor.removeMark).toHaveBeenCalledWith("anchor");
			});
		});

		describe("toggleAnchor", () => {
			it("inserts the mark when href is provided", () => {
				const { editor, mark } = setup({ command: vi.fn() });

				mark.commands().toggleAnchor({ href: "https://example.com" });

				expect(editor.command).toHaveBeenCalledWith("insertAnchor", {
					href: "https://example.com"
				});
			});

			it("removes the mark when href is empty", () => {
				const { editor, mark } = setup({ command: vi.fn() });

				mark.commands().toggleAnchor({ href: "" });

				expect(editor.command).toHaveBeenCalledWith("removeAnchor");
			});

			it("removes the mark when no attrs are provided", () => {
				const { editor, mark } = setup({ command: vi.fn() });

				mark.commands().toggleAnchor();

				expect(editor.command).toHaveBeenCalledWith("removeAnchor");
			});
		});
	});

	describe("plugins", () => {
		const attrs = { href: "https://example.com" };

		it("opens the href when alt-clicking the mark", () => {
			const { mark } = setup({ getMarkAttrs: vi.fn(() => attrs) });

			const event = click(mark, { altKey: true });

			expect(mark.opened).toHaveBeenCalledWith(attrs);
			expect(event.stopPropagation).toHaveBeenCalled();
		});

		it("does not open the href without the alt key", () => {
			const { mark } = setup({ getMarkAttrs: vi.fn(() => attrs) });

			click(mark);

			expect(mark.opened).not.toHaveBeenCalled();
		});

		it("does not open the href when the click is not on a link", () => {
			const { mark } = setup({ getMarkAttrs: vi.fn(() => attrs) });

			click(mark, { altKey: true, target: document.createElement("span") });

			expect(mark.opened).not.toHaveBeenCalled();
		});

		it("does not open without an href", () => {
			const { mark } = setup({ getMarkAttrs: vi.fn(() => ({ href: "" })) });

			click(mark, { altKey: true });

			expect(mark.opened).not.toHaveBeenCalled();
		});

		it("does not open a rejected href", () => {
			const { mark } = setup({
				getMarkAttrs: vi.fn(() => ({ href: "rejected" }))
			});

			click(mark, { altKey: true });

			expect(mark.opened).not.toHaveBeenCalled();
		});
	});
});
