import { describe, expect, it, vi } from "vitest";
import { DOMParser } from "prosemirror-model";
import { createSchemaWithNodes, toHTML } from "@test/unit/editor";
import utils from "../Utils";
import BulletList from "./BulletList";
import ListItem from "./ListItem";

const node = new ListItem();
const schema = createSchemaWithNodes({
	bulletList: new BulletList().schema,
	listItem: node.schema
});
const context = { type: schema.nodes.listItem, schema, utils };

describe("ListItem node", () => {
	describe("keys", () => {
		it("maps Enter to splitListItem", () => {
			const splitListItem = vi.fn(() => vi.fn());
			const keys = node.keys({
				...context,
				utils: { ...utils, splitListItem }
			});
			expect(keys["Enter"]).toBeDefined();
			expect(splitListItem).toHaveBeenCalledWith(schema.nodes.listItem);
		});

		it("maps Shift-Tab to liftListItem", () => {
			const liftListItem = vi.fn(() => vi.fn());
			const keys = node.keys({
				...context,
				utils: { ...utils, liftListItem }
			});
			expect(keys["Shift-Tab"]).toBeDefined();
			expect(liftListItem).toHaveBeenCalledWith(schema.nodes.listItem);
		});

		it("maps Tab to sinkListItem", () => {
			const sinkListItem = vi.fn(() => vi.fn());
			const keys = node.keys({
				...context,
				utils: { ...utils, sinkListItem }
			});
			expect(keys["Tab"]).toBeDefined();
			expect(sinkListItem).toHaveBeenCalledWith(schema.nodes.listItem);
		});
	});

	describe("name", () => {
		it("returns 'listItem'", () => {
			expect(node.name).toBe("listItem");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses <li> as listItem node", () => {
				const div = document.createElement("div");
				div.innerHTML = "<ul><li><p>foo</p></li></ul>";
				const doc = DOMParser.fromSchema(schema).parse(div);
				expect(doc.firstChild?.type).toBe(schema.nodes.bulletList);
				expect(doc.firstChild?.firstChild?.type).toBe(schema.nodes.listItem);
			});
		});

		describe("toDOM", () => {
			it("renders the listItem node as <li>", () => {
				const node = schema.nodes.listItem.create(null, [
					schema.nodes.paragraph.create(null, [schema.text("foo")])
				]);
				const list = schema.nodes.bulletList.create(null, [node]);
				const doc = schema.node("doc", null, [list]);
				expect(toHTML(schema, doc)).toBe("<ul><li><p>foo</p></li></ul>");
			});
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(node.type).toBe("node");
		});
	});
});
