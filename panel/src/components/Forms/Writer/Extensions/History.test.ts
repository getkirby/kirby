import { describe, expect, it } from "vitest";
import { Plugin } from "prosemirror-state";
import { undo, redo, undoDepth, redoDepth } from "prosemirror-history";
import History from "./History";

describe("History", () => {
	describe("constructor", () => {
		it("uses defaults when no options are provided", () => {
			expect(new History().options).toStrictEqual({
				depth: undefined,
				newGroupDelay: undefined
			});
		});

		it("merges provided options with defaults", () => {
			expect(new History({ depth: 50 }).options).toStrictEqual({
				depth: 50,
				newGroupDelay: undefined
			});
		});
	});

	describe("commands", () => {
		it("undo returns the undo function", () => {
			expect(new History().commands().undo()).toBe(undo);
		});

		it("redo returns the redo function", () => {
			expect(new History().commands().redo()).toBe(redo);
		});

		it("undoDepth returns the undoDepth function", () => {
			expect(new History().commands().undoDepth()).toBe(undoDepth);
		});

		it("redoDepth returns the redoDepth function", () => {
			expect(new History().commands().redoDepth()).toBe(redoDepth);
		});
	});

	describe("keys", () => {
		it("maps Mod-z to undo", () => {
			expect(new History().keys()["Mod-z"]).toBe(undo);
		});

		it("maps Mod-y to redo", () => {
			expect(new History().keys()["Mod-y"]).toBe(redo);
		});

		it("maps Shift-Mod-z to redo", () => {
			expect(new History().keys()["Shift-Mod-z"]).toBe(redo);
		});

		it("maps Mod-я to undo", () => {
			expect(new History().keys()["Mod-я"]).toBe(undo);
		});

		it("maps Shift-Mod-я to redo", () => {
			expect(new History().keys()["Shift-Mod-я"]).toBe(redo);
		});
	});

	describe("name", () => {
		it("returns 'history'", () => {
			expect(new History().name).toBe("history");
		});
	});

	describe("plugins", () => {
		it("returns one Plugin instance", () => {
			const plugins = new History().plugins();
			expect(plugins).toHaveLength(1);
			expect(plugins[0]).toBeInstanceOf(Plugin);
		});
	});

	describe("type", () => {
		it("returns 'extension'", () => {
			expect(new History().type).toBe("extension");
		});
	});
});
