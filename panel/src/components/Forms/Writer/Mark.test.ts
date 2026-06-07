import { describe, expect, it, vi } from "vitest";
import type { MarkSpec } from "prosemirror-model";
import type Editor from "./Editor";
import Mark, { type MarkContext } from "./Mark";

class TestMark extends Mark {
	get name() {
		return "bold";
	}
	get schema(): MarkSpec {
		return {};
	}
}

const mockEditor = (overrides = {}) =>
	({
		removeMark: vi.fn(),
		toggleMark: vi.fn(),
		updateMark: vi.fn(),
		...overrides
	}) as unknown as Editor;

const mark = new TestMark();
const context = {} as MarkContext;
const editor = mockEditor();
mark.bindEditor(editor);

describe("Mark", () => {
	describe("button", () => {
		it("returns undefined by default", () => {
			expect(mark.button).toBeUndefined();
		});
	});

	describe("commands", () => {
		it("returns an empty object by default", () => {
			expect(mark.commands(context)).toStrictEqual({});
		});
	});

	describe("inputRules", () => {
		it("returns an empty array by default", () => {
			expect(mark.inputRules(context)).toStrictEqual([]);
		});
	});

	describe("keys", () => {
		it("returns an empty object by default", () => {
			expect(mark.keys(context)).toStrictEqual({});
		});
	});

	describe("pasteRules", () => {
		it("returns an empty array by default", () => {
			expect(mark.pasteRules(context)).toStrictEqual([]);
		});
	});

	describe("plugins", () => {
		it("returns an empty array by default", () => {
			expect(mark.plugins()).toStrictEqual([]);
		});
	});

	describe("remove", () => {
		it("calls editor.removeMark with the mark name", () => {
			mark.remove();
			expect(editor.removeMark).toHaveBeenCalledWith("bold");
		});
	});

	describe("toggle", () => {
		it("calls editor.toggleMark with the mark name", () => {
			mark.toggle();
			expect(editor.toggleMark).toHaveBeenCalledWith("bold");
		});
	});

	describe("type", () => {
		it("returns 'mark'", () => {
			expect(mark.type).toBe("mark");
		});
	});

	describe("update", () => {
		it("calls editor.updateMark with the mark name and attrs", () => {
			mark.update({ href: "https://example.com" });
			expect(editor.updateMark).toHaveBeenCalledWith("bold", {
				href: "https://example.com"
			});
		});
	});

	describe("view", () => {
		it("returns undefined by default", () => {
			expect(mark.view).toBeUndefined();
		});
	});
});
