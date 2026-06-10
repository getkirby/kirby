import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	applyInputRule,
	createSchemaWithNodes,
	getNodeAttrs,
	hasNode,
	toHTML
} from "@test/unit/editor";
import utils from "../Utils";
import ListItem from "./ListItem";
import OrderedList from "./OrderedList";

const node = new OrderedList();
const schema = createSchemaWithNodes({
	listItem: new ListItem().schema,
	orderedList: node.schema
});
const context = { type: schema.nodes.orderedList, schema, utils };

describe("OrderedList node", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = node.button;
			expect(button.icon).toBe("list-numbers");
			expect(button.id).toBe("orderedList");
			expect(button.name).toBe("orderedList");
			expect(button.label).toBeDefined();
			expect(button.separator).toBe(true);
		});
	});

	describe("commands", () => {
		it("returns a command that toggles the orderedList", () => {
			const toggleList = vi.fn();
			const command = node.commands({
				...context,
				utils: { ...utils, toggleList }
			});
			command();
			expect(toggleList).toHaveBeenCalledWith(
				schema.nodes.orderedList,
				schema.nodes.listItem
			);
		});
	});

	describe("inputRules", () => {
		const [rule] = node.inputRules(context);

		it("wraps in orderedList when '1. ' is typed", () => {
			const result = applyInputRule(schema, rule, "1. ", "foo");
			expect(result).toBe("<ol><li><p>foo</p></li></ol>");
		});

		it("sets the order attribute when '3. ' is typed", () => {
			const result = applyInputRule(schema, rule, "3. ", "foo");
			expect(result).toBe('<ol start="3"><li><p>foo</p></li></ol>');
		});

		it("does not trigger for non-numeric input", () => {
			const result = applyInputRule(schema, rule, "foo. ");
			expect(result).toBeNull();
		});
	});

	describe("keys", () => {
		it("maps Shift-Ctrl-9 to toggleList", () => {
			const toggleList = vi.fn(() => vi.fn());
			const keys = node.keys({
				...context,
				utils: { ...utils, toggleList }
			});
			expect(keys["Shift-Ctrl-9"]).toBeDefined();
			expect(toggleList).toHaveBeenCalledWith(
				schema.nodes.orderedList,
				schema.nodes.listItem
			);
		});
	});

	describe("name", () => {
		it("returns 'orderedList'", () => {
			expect(node.name).toBe("orderedList");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses <ol> as orderedList node", () => {
				const html = "<ol><li>foo</li></ol>";
				expect(hasNode(schema, html, schema.nodes.orderedList)).toBe(true);
			});

			it("defaults order to 1 when no start attribute", () => {
				const attrs = getNodeAttrs(
					schema,
					"<ol><li>foo</li></ol>",
					schema.nodes.orderedList
				);
				expect(attrs?.order).toBe(1);
			});

			it("reads order from the start attribute", () => {
				const attrs = getNodeAttrs(
					schema,
					'<ol start="3"><li>foo</li></ol>',
					schema.nodes.orderedList
				);
				expect(attrs?.order).toBe(3);
			});
		});

		describe("toDOM", () => {
			it("renders as <ol> when order is 1", () => {
				const listItem = schema.nodes.listItem.create(null, [
					schema.nodes.paragraph.create(null, [schema.text("foo")])
				]);
				const list = schema.nodes.orderedList.create({ order: 1 }, [listItem]);
				const doc = schema.node("doc", null, [list]);
				expect(toHTML(schema, doc)).toBe("<ol><li><p>foo</p></li></ol>");
			});

			it("renders as <ol start> when order is not 1", () => {
				const listItem = schema.nodes.listItem.create(null, [
					schema.nodes.paragraph.create(null, [schema.text("foo")])
				]);
				const list = schema.nodes.orderedList.create({ order: 3 }, [listItem]);
				const doc = schema.node("doc", null, [list]);
				expect(toHTML(schema, doc)).toBe(
					'<ol start="3"><li><p>foo</p></li></ol>'
				);
			});
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(node.type).toBe("node");
		});
	});
});
