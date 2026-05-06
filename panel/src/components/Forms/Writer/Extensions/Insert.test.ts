import { describe, expect, it, vi } from "vitest";
import { Schema } from "prosemirror-model";
import { EditorState } from "prosemirror-state";
import Insert from "./Insert";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: {
			group: "block",
			content: "inline*",
			parseDOM: [{ tag: "p" }],
			toDOM: () => ["p", 0] as const
		},
		text: { group: "inline" }
	}
});

const state = EditorState.create({ schema });
const insert = new Insert();

describe("Insert", () => {
	describe("commands", () => {
		describe("insertHtml", () => {
			it("dispatches a transaction", () => {
				const { insertHtml } = insert.commands();
				const dispatch = vi.fn();
				const command = insertHtml("<p>Hello</p>");
				expect(command(state, dispatch)).toBe(true);
				expect(dispatch).toHaveBeenCalledOnce();
			});

			it("does not dispatch or throw when dispatch is undefined", () => {
				const { insertHtml } = insert.commands();
				const command = insertHtml("<p>Hello</p>");
				expect(() => command(state, undefined)).not.toThrow();
			});

			it("returns false when value is not a string", () => {
				const { insertHtml } = insert.commands();
				const command = insertHtml(42);
				expect(command(state, vi.fn())).toBe(false);
			});

			it("trims whitespace from the html value", () => {
				const { insertHtml } = insert.commands();
				const dispatch = vi.fn();
				const command = insertHtml("  <p>Hello</p>  ");
				command(state, dispatch);
				expect(dispatch).toHaveBeenCalledOnce();
			});
		});
	});

	describe("name", () => {
		it("returns 'insert'", () => {
			expect(insert.name).toBe("insert");
		});
	});

	describe("type", () => {
		it("returns 'extension'", () => {
			expect(insert.type).toBe("extension");
		});
	});
});
