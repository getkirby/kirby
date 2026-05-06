import { describe, expect, it } from "vitest";
import { Plugin } from "prosemirror-state";
import { undo, redo, undoDepth, redoDepth } from "prosemirror-history";
import History from "./History";

const history = new History();

describe("History", () => {
	describe("constructor", () => {
		it("uses defaults when no options are provided", () => {
			expect(history.options).toStrictEqual({
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
			expect(history.commands().undo()).toBe(undo);
		});

		it("redo returns the redo function", () => {
			expect(history.commands().redo()).toBe(redo);
		});

		it("undoDepth returns the undoDepth function", () => {
			expect(history.commands().undoDepth()).toBe(undoDepth);
		});

		it("redoDepth returns the redoDepth function", () => {
			expect(history.commands().redoDepth()).toBe(redoDepth);
		});
	});

	describe("keys", () => {
		it("maps Mod-z to undo", () => {
			expect(history.keys()["Mod-z"]).toBe(undo);
		});

		it("maps Mod-y to redo", () => {
			expect(history.keys()["Mod-y"]).toBe(redo);
		});

		it("maps Shift-Mod-z to redo", () => {
			expect(history.keys()["Shift-Mod-z"]).toBe(redo);
		});

		it("maps Mod-я to undo", () => {
			expect(history.keys()["Mod-я"]).toBe(undo);
		});

		it("maps Shift-Mod-я to redo", () => {
			expect(history.keys()["Shift-Mod-я"]).toBe(redo);
		});
	});

	describe("name", () => {
		it("returns 'history'", () => {
			expect(history.name).toBe("history");
		});
	});

	describe("plugins", () => {
		it("returns one Plugin instance", () => {
			const plugins = history.plugins();
			expect(plugins).toHaveLength(1);
			expect(plugins[0]).toBeInstanceOf(Plugin);
		});
	});

	describe("type", () => {
		it("returns 'extension'", () => {
			expect(history.type).toBe("extension");
		});
	});
});
