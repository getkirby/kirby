import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	createSchemaWithNodes,
	hasNode,
	mockEditor,
	toHTML
} from "@test/unit/editor";
import utils from "../Utils";
import Paragraph from "./Paragraph";

const node = new Paragraph();
const schema = createSchemaWithNodes({
	quote: { content: "block+", group: "block" },
	bulletList: { content: "block+", group: "block" },
	orderedList: { content: "block+", group: "block" },
	listItem: { content: "block+", group: "block" },
	paragraph: node.schema
});
const context = { type: schema.nodes.paragraph, schema, utils };

function withActiveNodes(activeNodes: string[]) {
	const editor = mockEditor({ activeNodes });
	node.bindEditor(editor);
}

describe("Paragraph node", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = node.button;
			expect(button.icon).toBe("paragraph");
			expect(button.id).toBe("paragraph");
			expect(button.name).toBe("paragraph");
			expect(button.label).toBeDefined();
			expect(button.separator).toBe(true);
		});
	});

	describe("commands", () => {
		describe("paragraph", () => {
			it("toggles list when inside a bulletList", () => {
				const toggleList = vi.fn();
				withActiveNodes(["bulletList"]);
				const { paragraph } = node.commands({
					...context,
					utils: { ...utils, toggleList }
				});
				paragraph();
				expect(toggleList).toHaveBeenCalledWith(
					schema.nodes.bulletList,
					schema.nodes.listItem
				);
			});

			it("toggles list when inside an orderedList", () => {
				const toggleList = vi.fn();
				withActiveNodes(["orderedList"]);
				const { paragraph } = node.commands({
					...context,
					utils: { ...utils, toggleList }
				});
				paragraph();
				expect(toggleList).toHaveBeenCalledWith(
					schema.nodes.orderedList,
					schema.nodes.listItem
				);
			});

			it("toggles wrap when inside a quote", () => {
				const toggleWrap = vi.fn();
				withActiveNodes(["quote"]);
				const { paragraph } = node.commands({
					...context,
					utils: { ...utils, toggleWrap }
				});
				paragraph();
				expect(toggleWrap).toHaveBeenCalledWith(schema.nodes.quote);
			});

			it("sets block type to paragraph when not in a list or quote", () => {
				const setBlockType = vi.fn();
				withActiveNodes([]);
				const { paragraph } = node.commands({
					...context,
					utils: { ...utils, setBlockType }
				});
				paragraph();
				expect(setBlockType).toHaveBeenCalledWith(schema.nodes.paragraph);
			});
		});
	});

	describe("name", () => {
		it("returns 'paragraph'", () => {
			expect(node.name).toBe("paragraph");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses <p> as paragraph node", () => {
				const html = "<p>foo</p>";
				expect(hasNode(schema, html, schema.nodes.paragraph)).toBe(true);
			});
		});

		describe("toDOM", () => {
			it("renders the paragraph node as <p>", () => {
				const para = schema.nodes.paragraph.create(null, [schema.text("foo")]);
				const doc = schema.node("doc", null, [para]);
				expect(toHTML(schema, doc)).toBe("<p>foo</p>");
			});
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(node.type).toBe("node");
		});
	});
});
