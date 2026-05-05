import { describe, expect, it } from "vitest";
import Extension from "./Extension";

class TestExtension extends Extension {
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

describe("Extension", () => {
	describe("constructor", () => {
		it("merges provided options with defaults", () => {
			const ext = new TestExtension({ size: 42 });
			expect(ext.options).toStrictEqual({ color: "red", size: 42 });
		});

		it("uses defaults when no options are provided", () => {
			const ext = new TestExtension();
			expect(ext.options).toStrictEqual({ color: "red", size: 10 });
		});
	});

	describe("bindEditor", () => {
		it("sets the editor reference", () => {
			const ext = new TestExtension();
			const fakeEditor = {};
			ext.bindEditor(fakeEditor);
			expect(ext.editor).toBe(fakeEditor);
		});
	});

	describe("inputRules", () => {
		it("returns an empty array by default", () => {
			expect(new BareExtension().inputRules()).toStrictEqual([]);
		});
	});

	describe("keys", () => {
		it("returns an empty object by default", () => {
			expect(new BareExtension().keys()).toStrictEqual({});
		});
	});

	describe("pasteRules", () => {
		it("returns an empty array by default", () => {
			expect(new BareExtension().pasteRules()).toStrictEqual([]);
		});
	});

	describe("plugins", () => {
		it("returns an empty array by default", () => {
			expect(new BareExtension().plugins()).toStrictEqual([]);
		});
	});

	describe("type", () => {
		it("returns 'extension'", () => {
			expect(new BareExtension().type).toBe("extension");
		});
	});
});
