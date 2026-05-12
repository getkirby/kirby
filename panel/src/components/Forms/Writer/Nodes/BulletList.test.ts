import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	applyInputRule,
	createSchemaWithNodes,
	hasNode,
	toHTML
} from "@test/unit/editor";
import utils from "../Utils";
import BulletList from "./BulletList";
import ListItem from "./ListItem";

const node = new BulletList();
const schema = createSchemaWithNodes({
	listItem: new ListItem().schema,
	bulletList: node.schema
});
const context = { type: schema.nodes.bulletList, schema, utils };

describe("BulletList node", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = node.button;
			expect(button.icon).toBe("list-bullet");
			expect(button.id).toBe("bulletList");
			expect(button.name).toBe("bulletList");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("returns a command that toggles the bulletList", () => {
			const toggleList = vi.fn();
			const command = node.commands({
				...context,
				utils: { ...utils, toggleList }
			});
			command();
			expect(toggleList).toHaveBeenCalledWith(
				schema.nodes.bulletList,
				schema.nodes.listItem
			);
		});
	});

	describe("inputRules", () => {
		const [rule] = node.inputRules(context);

		it("wraps in bulletList when '- ' is typed", () => {
			const result = applyInputRule(schema, rule, "- ", "foo");
			expect(result).toBe("<ul><li><p>foo</p></li></ul>");
		});

		it("wraps in bulletList when '+ ' is typed", () => {
			const result = applyInputRule(schema, rule, "+ ", "foo");
			expect(result).toBe("<ul><li><p>foo</p></li></ul>");
		});

		it("wraps in bulletList when '* ' is typed", () => {
			const result = applyInputRule(schema, rule, "* ", "foo");
			expect(result).toBe("<ul><li><p>foo</p></li></ul>");
		});
	});

	describe("keys", () => {
		it("maps Shift-Ctrl-8 to toggleList", () => {
			const toggleList = vi.fn(() => vi.fn());
			const keys = node.keys({
				...context,
				utils: { ...utils, toggleList }
			});
			expect(keys["Shift-Ctrl-8"]).toBeDefined();
			expect(toggleList).toHaveBeenCalledWith(
				schema.nodes.bulletList,
				schema.nodes.listItem
			);
		});
	});

	describe("name", () => {
		it("returns 'bulletList'", () => {
			expect(node.name).toBe("bulletList");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses <ul> as bulletList node", () => {
				const html = "<ul><li>foo</li></ul>";
				expect(hasNode(schema, html, schema.nodes.bulletList)).toBe(true);
			});
		});

		describe("toDOM", () => {
			it("renders the bulletList node as <ul>", () => {
				const listItem = schema.nodes.listItem.create(null, [
					schema.nodes.paragraph.create(null, [schema.text("foo")])
				]);
				const list = schema.nodes.bulletList.create(null, [listItem]);
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
