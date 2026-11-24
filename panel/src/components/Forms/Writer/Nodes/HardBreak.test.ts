import { describe, expect, it, vi } from "vitest";
import { createSchemaWithNodes, parseHTML, toHTML } from "@test/unit/editor";
import utils from "../Utils";
import HardBreak from "./HardBreak";

const node = new HardBreak();
const schema = createSchemaWithNodes({ hardBreak: node.schema });
const context = { type: schema.nodes.hardBreak, schema, utils };

describe("HardBreak node", () => {
	describe("commands", () => {
		it("chains exitCode and insertNode", () => {
			const insertedCommand = vi.fn();
			const insertNode = vi.fn().mockReturnValue(insertedCommand);
			const exitCode = vi.fn();
			const chainCommands = vi.fn();
			const command = node.commands({
				...context,
				utils: { ...utils, insertNode, exitCode, chainCommands }
			});
			command();
			expect(insertNode).toHaveBeenCalledWith(schema.nodes.hardBreak);
			expect(chainCommands).toHaveBeenCalledWith(exitCode, insertedCommand);
		});
	});

	describe("defaults", () => {
		it("has enter set to false", () => {
			expect(node.defaults.enter).toBe(false);
		});
	});

	describe("keys", () => {
		it("maps Mod-Enter to the hard break command", () => {
			const keys = node.keys(context);
			expect(keys["Mod-Enter"]).toBeDefined();
		});

		it("maps Shift-Enter to the hard break command", () => {
			const keys = node.keys(context);
			expect(keys["Shift-Enter"]).toBeDefined();
		});

		it("maps Mod-Enter and Shift-Enter to the same command", () => {
			const keys = node.keys(context);
			expect(keys["Mod-Enter"]).toBe(keys["Shift-Enter"]);
		});

		it("does not map Enter by default", () => {
			const keys = node.keys(context) as Record<string, unknown>;
			expect(keys["Enter"]).toBeUndefined();
		});

		describe("with enter option enabled", () => {
			const node = new HardBreak({ enter: true });

			it("maps Enter to the hard break command", () => {
				const keys = node.keys(context) as Record<string, unknown>;
				expect(keys["Enter"]).toBeDefined();
			});
		});
	});

	describe("name", () => {
		it("returns 'hardBreak'", () => {
			expect(node.name).toBe("hardBreak");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses <br> as hardBreak node", () => {
				const doc = parseHTML(schema, "<br>");
				expect(doc.firstChild?.firstChild?.type).toBe(schema.nodes.hardBreak);
			});
		});

		describe("toDOM", () => {
			it("renders the node as <br>", () => {
				const para = schema.node("paragraph", null, [
					schema.text("foo"),
					schema.nodes.hardBreak.create(),
					schema.text("bar")
				]);
				const doc = schema.node("doc", null, [para]);
				expect(toHTML(schema, doc)).toBe("<p>foo<br>bar</p>");
			});
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(node.type).toBe("node");
		});
	});
});
