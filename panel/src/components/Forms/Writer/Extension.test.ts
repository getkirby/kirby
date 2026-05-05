import { describe, expect, it } from "vitest";
import type Editor from "./Editor";
import Extension, { type BaseContext } from "./Extension";

class TestExtension extends Extension<{ color: string; size: number }> {
	get name() {
		return "test";
	}

	get defaults() {
		return { color: "red", size: 10 };
	}
}

class BareExtension extends Extension {
	get name() {
		return "bare";
	}
}

const ext = new TestExtension();
const bare = new BareExtension();
const context = {} as BaseContext;

describe("Extension", () => {
	describe("constructor", () => {
		it("merges provided options with defaults", () => {
			const ext = new TestExtension({ size: 42 });
			expect(ext.options).toStrictEqual({ color: "red", size: 42 });
		});

		it("uses defaults when no options are provided", () => {
			expect(ext.options).toStrictEqual({ color: "red", size: 10 });
		});
	});

	describe("bindEditor", () => {
		it("sets the editor reference", () => {
			const fakeEditor = {} as Editor;
			ext.bindEditor(fakeEditor);
			expect(ext.editor).toBe(fakeEditor);
		});
	});

	describe("button", () => {
		it("is undefined by default", () => {
			expect(bare.button).toBeUndefined();
		});
	});

	describe("commands", () => {
		it("returns an empty object by default", () => {
			expect(bare.commands(context)).toStrictEqual({});
		});
	});

	describe("inputRules", () => {
		it("returns an empty array by default", () => {
			expect(bare.inputRules(context)).toStrictEqual([]);
		});
	});

	describe("keys", () => {
		it("returns an empty object by default", () => {
			expect(bare.keys(context)).toStrictEqual({});
		});
	});

	describe("pasteRules", () => {
		it("returns an empty array by default", () => {
			expect(bare.pasteRules(context)).toStrictEqual([]);
		});
	});

	describe("plugins", () => {
		it("returns an empty array by default", () => {
			expect(bare.plugins()).toStrictEqual([]);
		});
	});

	describe("type", () => {
		it("returns 'extension'", () => {
			expect(bare.type).toBe("extension");
		});
	});
});
